<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Pack;
use App\Form\Type\PackType;
use App\Message\UploadMessage;
use App\Model\Status;
use App\Repository\PackRepository;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Sentry\captureException;

class PackController extends AbstractController
{
    #[Route(name: 'api_home', methods: ['GET'], path: '/api/public/galleries/latest')]
    public function getLatestPacks(
        PaginatorInterface $paginator,
        PackRepository $packRepository,
        NormalizerInterface $serializer
    ) {
        $pageData = $paginator->paginate(
            $packRepository->findBy(['status' => Status::OK], ['id' => 'desc']),
            1, // BR: For public API, page is forced to 1
            25
        );

        return new JsonResponse([
            'pagination' => [
                'current' => $pageData->getCurrentPageNumber(),
                'max' => ceil($pageData->getTotalItemCount() / $pageData->getItemNumberPerPage()),
                'total_items' => $pageData->getTotalItemCount(),
            ],
            'data' => $serializer->normalize($pageData->getItems(), context: ['groups' => 'overview'])
        ]);
    }

    #[Route(name: 'galleries_latest', methods: ['POST'], path: '/api/galleries')]
    public function getPaginatedPacks(
        Request $request,
        PaginatorInterface $paginator,
        PackRepository $packRepository,
        NormalizerInterface $serializer
    ) {
        $pageData = $paginator->paginate(
            $packRepository->findBy(['status' => Status::OK], ['id' => 'desc']),
            (int)$request->query->get('page', '1'),
            25
        );

        return new JsonResponse([
            'pagination' => [
                'current' => $pageData->getCurrentPageNumber(),
                'max' => ceil($pageData->getTotalItemCount() / $pageData->getItemNumberPerPage()),
                'total_items' => $pageData->getTotalItemCount(),
            ],
            'data' => $serializer->normalize($pageData->getItems(), context: ['groups' => 'overview'])
        ]);
    }

	#[Route(name: 'gallery', methods: ['GET'], path: '/api/gallery/{id}')]
	#[ParamConverter('pack', class: Pack::class)]
	public function getGallery(Pack $pack, NormalizerInterface $normalizer, EntityManagerInterface $entityManager): JsonResponse
	{
		$pack->incrementViews();
		$entityManager->flush();

		return new JsonResponse($normalizer->normalize($pack, context: ['groups' => 'export']));
	}

	#[Route('/api/gallery/new', methods: ['POST'], priority: 10)]
	public function newAction(
		Request $request,
		UploadManager $uploadManager,
		TranslatorInterface $translator,
		EntityManagerInterface $entityManager,
		MessageBusInterface $messageBus,
		Security $security
	): JsonResponse {
		$pack = new Pack();
		$form = $this->createForm(PackType::class, $pack);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			try {
				$pack->setStoragePath($uploadManager->moveUploadFilesToTempStorage($pack->getFiles()));
				$pack->setStatus(Status::PROCESSING_UPLOAD);
				$pack->setCreator($security->getUser());
				$entityManager->persist($pack);
				$entityManager->flush();

				$messageBus->dispatch(new UploadMessage($pack->getId()));

				return new JsonResponse(null, Response::HTTP_CREATED);
			} catch (\Exception $e) {
				captureException($e);

				return new JsonResponse(['An unexpected error has occured'], Response::HTTP_INTERNAL_SERVER_ERROR);
			}
		}
	}
}

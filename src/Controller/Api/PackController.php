<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\BannedPicture;
use App\Entity\Pack;
use App\Entity\Tag;
use App\Entity\User;
use App\Form\Type\PackType;
use App\Message\UploadMessage;
use App\Message\ValidateUploadMessage;
use App\Model\Status;
use App\Repository\PackRepository;
use App\Repository\PictureRepository;
use App\Repository\TagRepository;
use App\Service\FileManager;
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
    ): JsonResponse {
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
    ): JsonResponse {
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

	#[Route(name: 'gallery_tag', methods: ['GET'], path: '/api/gallery/tag/{tag}')]
	public function getTaggedGallery(
		Tag $tag,
		Request $request,
		Security $security,
		PaginatorInterface $paginator,
		NormalizerInterface $normalizer,
		PackRepository $packRepository,
		TagRepository $tagRepository,
		PictureRepository $pictureRepository
	):
	JsonResponse
	{
		$page = (int)$request->query->get('page', '1');

		$pageData = $paginator->paginate(
			$packRepository->getPacksByTag($tag),
			$page,
			25
		);

		$data = $normalizer->normalize($pageData->getItems(), context: ['groups' => 'overview']);
		if ($page === 1) {
			$randomPack = (new Pack())
				->setId('tag-' . $tag->getId() . '-random')
				->setCreated(new \DateTime())
				->setCreator($security->getUser())
				->setName('RANDOM')
				->setPictures($pictureRepository->findRandomByTag($tag));

			if (!is_array($data)) {
				throw new \UnexpectedValueException(sprintf('Expected array got %s', gettype($data)));
			}

			array_unshift($data, $normalizer->normalize($randomPack, context: ['groups' => 'overview']));
		}

		return new JsonResponse([
			'pagination' => [
				'current' => $pageData->getCurrentPageNumber(),
				'max' => ceil($pageData->getTotalItemCount() / $pageData->getItemNumberPerPage()),
				'total_items' => $pageData->getTotalItemCount(),
			],
			'data' => $data
		]);
	}

	#[Route(name: 'random_pictures', methods: ['GET'], path: '/api/gallery/random/{tag}')]
	public function getRandomPictures(
		Security $security,
		NormalizerInterface $normalizer,
		PictureRepository $pictureRepository,
		TagRepository $tagRepository,
		string $tag = null,
	): JsonResponse
	{
		if ($tag) {
			$tagEntity = $tagRepository->find($tag);
		} else {
			$tagEntity = null;
		}
		$temporaryPack = new Pack();
		$temporaryPack->setCreator($security->getUser())
			->setCreated(new \DateTime())
			->setName('Random')
			->setPictures($tagEntity ? $pictureRepository->findRandomByTag($tagEntity) : $pictureRepository->findRandom());

		return new JsonResponse($normalizer->normalize($temporaryPack, context: ['groups' => 'export']));
	}

	#[Route(name: 'gallery', methods: ['GET'], path: '/api/gallery/{id}')]
	#[ParamConverter('pack', class: Pack::class)]
	public function getGallery(Pack $pack, NormalizerInterface $normalizer, EntityManagerInterface $entityManager, Security $security): JsonResponse
	{
		$this->denyAccessUnlessGranted('view', $pack);

		/** @var User $user */
		$user = $security->getUser();

		$pack->incrementViews($user);
		$entityManager->flush();

		return new JsonResponse($normalizer->normalize($pack, context: ['groups' => 'export']));
	}

	#[Route(name: 'delete_gallery', methods: ['DELETE'], path: '/api/gallery/{id}')]
	#[ParamConverter('pack', class: Pack::class)]
	public function deleteGallery(Pack $pack, EntityManagerInterface $entityManager): JsonResponse
	{
		$this->denyAccessUnlessGranted('delete', $pack);

		$entityManager->remove($pack);
		$entityManager->flush();

		return new JsonResponse(null, Response::HTTP_ACCEPTED);
	}

	#[Route('/api/admin/gallery/{pack}/ban', name: 'gallery_ban', methods: ['GET'])]
	public function banAction(
		Pack $pack,
		EntityManagerInterface $entityManager,
		FileManager $fileManager,
	): JsonResponse {
		foreach ($pack->getPictures() as $picture) {
			$bannedPicture = new BannedPicture($picture->getSha1sum());
			$entityManager->persist($bannedPicture);
			$entityManager->remove($picture);
			$fileManager->deletePicture($picture);
			$entityManager->flush();
		}

		return new JsonResponse(null, Response::HTTP_ACCEPTED);
	}

	#[Route('/api/admin/gallery/{pack}/approve', name:'gallery_confirm', methods: ['GET'])]
	public function publishAction(Pack $pack, EntityManagerInterface $entityManager, MessageBusInterface $messageBus): JsonResponse
	{
		$pack->setStatus(Status::PROCESSING_VALIDATION);
		$entityManager->flush();
		$messageBus->dispatch(new ValidateUploadMessage($pack->getId()));

		return new JsonResponse(null, Response::HTTP_ACCEPTED);
	}

	#[Route('/api/gallery/new', methods: ['POST'], priority: 10)]
	public function newAction(
		Request $request,
		UploadManager $uploadManager,
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

		return new JsonResponse(['No data found'], Response::HTTP_BAD_REQUEST);
	}
}

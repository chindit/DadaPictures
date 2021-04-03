<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Status;
use App\Repository\PackRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class IndexController extends AbstractController
{
    #[Route('/api/packs', name:'homepage', methods:['GET'])]
    public function indexAction(
        PackRepository $packRepository,
        PaginatorInterface $paginator,
        NormalizerInterface $normalizer,
        Request $request
    ): Response {
        $pagination = $paginator->paginate(
            $packRepository->findBy(['status' => Status::OK], ['id' => 'desc']),
            (int)$request->query->get('page', '1'),
            25
        );

        return new JsonResponse([
        	'data' => $normalizer->normalize($pagination->getItems(), null, [AbstractNormalizer::IGNORED_ATTRIBUTES => ['creator']]),
	        'pagination' => [
	        	'per_page' => $pagination->getItemNumberPerPage(),
		        'page' => $pagination->getCurrentPageNumber(),
		        'total' => $pagination->getTotalItemCount(),
	        ]
        ]);
    }

    #[Route('/terms', name:'terms_and_conditions', methods:['GET'])]
    public function termsAndConditions(): Response
    {
        return $this->render('default/terms.html.twig');
    }
}

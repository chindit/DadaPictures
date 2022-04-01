<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Status;
use App\Repository\PackRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
}

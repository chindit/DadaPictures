<?php

namespace App\Controller\Api;

use App\Repository\PackRepository;
use Chindit\Collection\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdminController extends AbstractController
{
    #[Route(name: 'admin_packs_validation', methods: ['GET'], path: '/api/admin/validation/galleries')]
    public function getPacksInValidation(PackRepository $packRepository, NormalizerInterface $normalizer): JsonResponse
    {
		$packs = (new Collection($normalizer->normalize($packRepository->getPacksInValidation(), context: ['groups' => ['export']])))
			->groupBy('status');

        return new JsonResponse($packs->toArray());
    }
}

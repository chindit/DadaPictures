<?php

namespace App\Controller\Api;

use App\Entity\Picture;
use App\Repository\PackRepository;
use App\Service\BanService;
use Chindit\Collection\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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

	#[Route(path: '/api/admin/picture/{id}/ban', name: 'api_ban_picture', methods: ['GET'])]
	#[ParamConverter('picture', Picture::class)]
	public function banPicture(Picture $picture, BanService $banService): JsonResponse
	{
		$banService->banPicture($picture);

		return new JsonResponse(null, Response::HTTP_ACCEPTED);
	}

	#[Route(path: '/api/admin/picture/{id}', name: 'api_delete_picture', methods: ['DELETE'])]
	#[ParamConverter('picture', Picture::class)]
	public function deletePicture(Picture $picture, EntityManagerInterface $entityManager): JsonResponse
	{
		$entityManager->remove($picture);
		$entityManager->flush();

		return new JsonResponse(null, Response::HTTP_ACCEPTED);
	}
}

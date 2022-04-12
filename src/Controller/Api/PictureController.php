<?php

namespace App\Controller\Api;

use App\Repository\PictureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PictureController extends AbstractController
{
	#[Route(path: '/api/picture/untagged', name: 'api_untagged_picture', methods: ['GET'])]
	public function viewUntaggedAction(PictureRepository $pictureRepository, NormalizerInterface $normalizer): JsonResponse
	{
		$picture = $pictureRepository->getPictureWithoutTags();

		return new JsonResponse($normalizer->normalize($picture, context: ['groups' => ['export']]));
	}
}

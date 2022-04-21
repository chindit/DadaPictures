<?php

namespace App\Controller\Api;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use App\Repository\TagRepository;
use App\Service\Path;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PictureController extends AbstractController
{
	#[Route(path: '/api/picture/untagged', name: 'api_untagged_picture', methods: ['GET'])]
	public function viewUntaggedAction(PictureRepository $pictureRepository, NormalizerInterface $normalizer): JsonResponse
	{
		$picture = $pictureRepository->getPictureWithoutTags();

		return new JsonResponse($normalizer->normalize($picture, context: ['groups' => ['export']]));
	}

	#[Route(path: '/api/picture/view/{picture}', name:'api_view_picture', methods: ['GET'])]
	public function viewPicture(Picture $picture, Path $path, EntityManagerInterface $entityManager, Security $security): Response
	{
		$picture->incrementViews($security->getUser());
		$entityManager->flush();

		return new Response(
			file_get_contents($path->getPictureFullpath($picture)) ?: '',
			Response::HTTP_OK,
			['Content-Type' => $picture->getMime()]
		);
	}

	#[Route(path: '/api/picture/{id}/tag', name: 'api_tag_picture', methods: ['POST'])]
	#[ParamConverter('picture', Picture::class)]
	public function tagPicture(EntityManagerInterface $entityManager, TagRepository $tagRepository, Picture $picture, Request $request): JsonResponse
	{
		$tags = $tagRepository->findBy(['id' => json_decode($request->getContent(), true)['tags']]);

		if (empty($tags)) {
			return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
		}

		foreach ($tags as $tag) {
			$picture->addTag($tag);
		}

		$entityManager->flush();

		return new JsonResponse(null, Response::HTTP_CREATED);
	}
}

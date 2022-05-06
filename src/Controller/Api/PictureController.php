<?php

namespace App\Controller\Api;

use App\Entity\Picture;
use App\Entity\User;
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
    #[Route(path: '/api/picture/untagged', name: 'api_untagged_picture', methods: ['GET'], priority: 10)]
    public function viewUntaggedAction(PictureRepository $pictureRepository, NormalizerInterface $normalizer): JsonResponse
    {
        $picture = $pictureRepository->getPictureWithoutTags();

        return new JsonResponse($normalizer->normalize($picture, context: ['groups' => ['export']]));
    }

    #[Route(path: '/api/picture/view/{picture}', name:'api_view_picture', methods: ['GET'], priority: 10)]
    public function viewPicture(Picture $picture, Path $path, EntityManagerInterface $entityManager, Security $security): Response
    {
        /** @var User $user */
        $user = $security->getUser();
        $picture->incrementViews($user);
        $entityManager->flush();

        return new Response(
            file_get_contents($path->getPictureFullpath($picture)) ?: '',
            Response::HTTP_OK,
            ['Content-Type' => $picture->getMime()]
        );
    }

	#[Route('/api/picture/view/thumb/{picture}', name: 'view_thumbnail_picture', methods: ['GET'])]
	public function viewThumbnail(Picture $picture, Path $path): Response
	{
		if (!$picture->getThumbnail()) {
			return $this->redirectToRoute('api_picture', ['picture' => $picture]);
		}

		return (new Response(
			file_get_contents($path->getThumbnailsDirectory() . $picture->getThumbnail()) ?: '',
			Response::HTTP_OK,
			['Content-Type' => 'image/jpg', 'Cache-Control' => 'max-age=3600']
		));
	}

    #[Route(path: '/api/picture/{picture}', name: 'api_picture', methods: ['GET'], priority: 5)]
    public function getPicture(Picture $picture, NormalizerInterface $normalizer): JsonResponse
    {
        return new JsonResponse($normalizer->normalize($picture, context: ['groups' => ['export']]));
    }

    #[Route(path: '/api/picture/{id}/tag', name: 'api_tag_picture', methods: ['POST'])]
    #[ParamConverter('picture', Picture::class)]
    public function tagPicture(EntityManagerInterface $entityManager, TagRepository $tagRepository, Picture $picture, Request $request): JsonResponse
    {
        $tags = $tagRepository->findBy(['id' => json_decode((string)$request->getContent(), true)['tags']]);

        if (empty($tags)) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        foreach ($tags as $tag) {
            if (!$picture->getTags()->contains($tag)) {
                $picture->addTag($tag);
            }
        }

        // Removing obsolete tags
        foreach ($picture->getTags() as $tag) {
            if (!in_array($tag, $tags)) {
                $picture->removeTag($tag);
            }
        }

        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}

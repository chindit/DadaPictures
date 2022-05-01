<?php

namespace App\Controller\Api;

use App\Entity\Tag;
use App\Entity\TranslatedTag;
use App\Model\Languages;
use App\Repository\TranslatedTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TagController extends AbstractController
{
    #[Route(name: 'api_tags', path: '/api/tags/{language}', methods: ['GET'])]
    public function getTags(string $language, TranslatedTagRepository $repository): Response
    {
        return new JsonResponse($repository->findAllWithTranslation($language));
    }

    #[Route(name: 'api_create_tag', path: '/api/tag/create', methods: ['POST'])]
    public function createTag(Request $request, ValidatorInterface $validator, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $constraints = [];

        foreach (Languages::all() as $language) {
            $constraints[$language] = [new Assert\Length(['min' => 3]), new Assert\NotBlank()];
        }

        $errors = $validator->validate($request->request->all(), new Assert\Collection($constraints));

        if ($errors->count() > 0) {
            return new JsonResponse($errors->get(0)->getMessage());
        }

        $tag = new Tag();
        foreach (Languages::all() as $language) {
            $tag->addTranslation(new TranslatedTag($language, (string)$request->request->get($language)));
        }
        $enTranslation = $tag->getTranslation(Languages::EN);
        if ($enTranslation === null) {
            throw new \UnexpectedValueException(sprintf('Tag %d has no translation', $tag->getId()));
        }
        $tag->setName($slugger->slug($enTranslation->getName()));

        $entityManager->persist($tag);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

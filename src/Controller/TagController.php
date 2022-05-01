<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\TranslatedTag;
use App\Model\Languages;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('tag')]
class TagController extends AbstractController
{
    #[Route('/', name: 'tag_index', methods: ['GET'])]
    public function indexAction(TagRepository $tagRepository): Response
    {
        $tags = $tagRepository->findAll();

        return $this->render('tag/index.html.twig', array(
            'tags' => $tags,
        ));
    }


    public function tagListAction(TagRepository $tagRepository): Response
    {
        $tags = $tagRepository->findAll();

        return $this->render('tagList.html.twig', ['tags' => $tags]);
    }

    #[Route('/new', name: 'tag_new', methods: ['GET', 'POST'])]
    public function newAction(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->request->has('tag')) {
            /** @var string|string[]|null $tagRequest */
            $tagRequest = $request->get('tag');
            if (!is_countable($tagRequest) || count($tagRequest) !== count(Languages::all())) {
                $this->addFlash('danger', 'Tag sent is not valid');

                return $this->render('tag/new.html.twig', ['languages' => Languages::all(),]);
            }

            $tag = new Tag();
            $tag->setName(uniqid(more_entropy: true));

            foreach (Languages::all() as $language) {
                $translatedTag = new TranslatedTag();
                $translatedTag->setLanguage($language);
                $translatedTag->setName($tagRequest[$language] ?? '');
                $tag->addTranslation($translatedTag);
                $entityManager->persist($translatedTag);
            }

            $entityManager->persist($tag);
            $entityManager->flush();

            $this->addFlash('info', 'Tag «' . $tag->getName() . '» successfully added');
        }

        return $this->render('tag/new.html.twig', ['languages' => Languages::all(),]);
    }

    #[Route('/{id}/edit', name: 'tag_edit', methods: ['GET', 'POST'])]
    public function editAction(Request $request, Tag $tag, EntityManagerInterface $entityManager): Response
    {
        $deleteForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('tag_delete', array('id' => $tag->getId())))
            ->setMethod('DELETE')
            ->getForm();
        if ($request->query->has('tag')) {
            /** @var string|string[]|null $tagRequest */
            $tagRequest = $request->query->get('tag');
            if (!is_countable($tagRequest) || count($tagRequest) !== count(Languages::all())) {
                $this->addFlash('danger', 'Tag sent is not valid');

                return $this->render('tag/new.html.twig', ['languages' => Languages::all(),]);
            }

            foreach (Languages::all() as $language) {
                $translatedTag = $tag->getTranslation($language, true) ?: new TranslatedTag();
                $translatedTag->setLanguage($language);
                $translatedTag->setName($tagRequest[$language]);
                $tag->addTranslation($translatedTag);
                $entityManager->persist($translatedTag);
            }

            $entityManager->persist($tag);
            $entityManager->flush();

            $this->addFlash('info', 'Tag «' . $tag->getName() . '» successfully edited');
        }

        return $this->render('tag/edit.html.twig', array(
            'tag' => $tag,
            'delete_form' => $deleteForm->createView(),
            'languages' => Languages::all()
        ));
    }

    #[Route('/{id}', name:'tag_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request, Tag $tag, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tag_delete', array('id' => $tag->getId())))
            ->setMethod('DELETE')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->remove($tag);
            $entityManager->flush();
        }

        return $this->redirectToRoute('tag_index');
    }
}

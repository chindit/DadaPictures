<?php
declare(strict_types=1);

namespace App\Controller;


use App\Entity\Tag;
use App\Form\Type\TagType;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
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


    public function tagListAction(TagRepository $tagRepository) : Response
    {
        $tags = $tagRepository->findAll();

        return $this->render('tagList.html.twig', ['tags' => $tags]);
    }

    #[Route('/new', name: 'tag_new', methods: ['GET', 'POST'])]
    public function newAction(Request $request, EntityManagerInterface $entityManager, FlashBagInterface $flashBag): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tag);
            $entityManager->flush();
            $flashBag->add('info', 'Tag «' . $tag->getName() . '» successfully added');
        }

        return $this->render('tag/new.html.twig', array('form' => $form->createView()));
    }

    #[Route('/{id}/edit', name: 'tag_edit', methods: ['GET', 'POST'])]
    public function editAction(Request $request, Tag $tag, EntityManagerInterface $entityManager): Response
    {
        $deleteForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('tag_delete', array('id' => $tag->getId())))
            ->setMethod('DELETE')
            ->getForm();
        $editForm = $this->createForm('App\Form\Type\TagType', $tag);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('tag_edit', array('id' => $tag->getId()));
        }

        return $this->render('tag/edit.html.twig', array(
            'tag' => $tag,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
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

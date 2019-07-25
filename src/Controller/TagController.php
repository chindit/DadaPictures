<?php
declare(strict_types=1);

namespace App\Controller;


use App\Entity\Tag;
use App\Form\Type\TagType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Tag controller.
 *
 * @Route("tag")
 */
class TagController extends Controller
{
    /**
     * Lists all tag entities.
     *
     * @Route("/", name="tag_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $tags = $em->getRepository('App:Tag')->findAll();

        return $this->render('tag/index.html.twig', array(
            'tags' => $tags,
        ));
    }


    public function tagListAction() : Response
    {
        $tags = $this->getDoctrine()->getRepository(Tag::class)->findAll();

        return $this->render('tagList.html.twig', ['tags' => $tags]);
    }

    /**
     * Creates a new tag entity.
     *
     * @Route("/new", name="tag_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($tag);
            $this->getDoctrine()->getManager()->flush();
            $this->get('session')->getFlashBag()->add('info', 'Tag «' . $tag->getName() . '» successfully added');
        }

        return $this->render('tag/new.html.twig', array('form' => $form->createView()));
    }

    /**
     * Displays a form to edit an existing tag entity.
     *
     * @Route("/{id}/edit", name="tag_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Tag $tag)
    {
        $deleteForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('tag_delete', array('id' => $tag->getId())))
            ->setMethod('DELETE')
            ->getForm();
        $editForm = $this->createForm('App\Form\Type\TagType', $tag);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tag_edit', array('id' => $tag->getId()));
        }

        return $this->render('tag/edit.html.twig', array(
            'tag' => $tag,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a tag entity.
     *
     * @Route("/{id}", name="tag_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Tag $tag)
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('tag_delete', array('id' => $tag->getId())))
            ->setMethod('DELETE')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($tag);
            $em->flush();
        }

        return $this->redirectToRoute('tag_index');
    }
}

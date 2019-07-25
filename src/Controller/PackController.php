<?php

namespace App\Controller;

use App\Entity\Pack;
use App\Form\Type\PreShowType;
use App\Form\Type\PackType;
use App\Service\UploadManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Pack controller.
 *
 * @Route("pack")
 */
class PackController extends Controller
{
    /**
     * Lists all pack entities.
     *
     * @Route("/", name="pack_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $packs = $em->getRepository('App:Pack')->findAll();

        return $this->render('pack/index.html.twig', array(
            'packs' => $packs,
        ));
    }

    /**
     * Creates a new pack entity.
     *
     * @Route("/new", name="pack_new")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $pack = new Pack();
        $form = $this->createForm(PackType::class, $pack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadManager = $this->get(UploadManager::class);
            try {
                /** @var Pack $pack */
                $pack = $uploadManager->upload($pack);

                return $this->redirectToRoute('pack_pre_show', array('id' => $pack->getId()));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', 'Unable to handle file upload');
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return $this->render('pack/new.html.twig', array(
            'pack' => $pack,
            'form' => $form->createView(),
        ));
    }

    /**
     * Display result of pack upload
     *
     * @param Pack $pack
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @internal param array $files
     *
     * @Route("/uploadConfirm/{id}", name="pack_pre_show")
     * @Method({"GET", "POST"})
     */
    public function preShowAction(Pack $pack, Request $request)
    {
        $form = $this->createForm(PreShowType::class, null, ['pack' => $pack]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->get('session')->getFlashBag()->add('danger', $form->getErrors()[0]->getMessage());
            } else {
                $this->get(UploadManager::class)->validateUpload($pack, $form->get('files')->getData());

                return $this->redirectToRoute('pack_index');
            }
        }

        return $this->render('pack/preUpload.html.twig', ['pack' => $pack, 'form' => $form->createView()]);
    }

    /**
     * Finds and displays a pack entity.
     *
     * @Route("/{id}", name="pack_show")
     * @Method("GET")
     */
    public function showAction(Pack $pack)
    {
        $deleteForm = $this->createDeleteForm($pack);

        return $this->render('pack/show.html.twig', array(
            'pack' => $pack,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing pack entity.
     *
     * @Route("/{id}/edit", name="pack_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Pack $pack)
    {
        $deleteForm = $this->createDeleteForm($pack);
        $editForm = $this->createForm('App\Form\Type\PackType', $pack);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('pack_edit', array('id' => $pack->getId()));
        }

        return $this->render('pack/edit.html.twig', array(
            'pack' => $pack,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }


    /**
     * Deletes a pack entity.
     *
     * @Route("/{id}", name="pack_delete")
     * @Method("DELETE")
     * @param Request $request
     * @param Pack $pack
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request, Pack $pack)
    {
        $form = $this->createDeleteForm($pack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($pack);
            $em->flush();
        }

        return $this->redirectToRoute('pack_index');
    }

    /**
     * Creates a form to delete a pack entity.
     *
     * @param Pack $pack The pack entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Pack $pack)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('pack_delete', array('id' => $pack->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}

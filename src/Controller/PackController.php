<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BannedPicture;
use App\Entity\Pack;
use App\Form\Type\PackType;
use App\Message\ValidateUploadMessage;
use App\Model\Status;
use App\Service\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class PackController extends AbstractController
{
    #[Route('/pack/{pack}/ban', name: 'pack_ban', methods: ['GET'])]
    public function banAction(
        Pack $pack,
        EntityManagerInterface $entityManager,
        FileManager $fileManager,
    ): Response {
        foreach ($pack->getPictures() as $picture) {
            $bannedPicture = new BannedPicture($picture->getSha1sum());
            $entityManager->persist($bannedPicture);
            $entityManager->remove($picture);
            $fileManager->deletePicture($picture);
            $entityManager->flush();
        }

        $this->addFlash('info', 'Pack «' . $pack->getName() . '» has
            been correctly banned');

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/pack/{id}/confirm', name:'pack_confirm', methods: ['GET'])]
    public function publishAction(Pack $pack, EntityManagerInterface $entityManager, MessageBusInterface $messageBus): Response
    {
        $pack->setStatus(Status::PROCESSING_VALIDATION);
        $entityManager->flush();
        $messageBus->dispatch(new ValidateUploadMessage($pack->getId()));

        return $this->redirectToRoute('homepage');
    }

    #[Route('/pack/{id}', name: 'pack_show', methods: ['GET'])]
    public function showAction(Pack $pack, EntityManagerInterface $entityManager): Response
    {
        $pack->incrementViews();
        $entityManager->flush();

        $deleteForm = $this->createDeleteForm($pack);

        return $this->render('pack/show.html.twig', array(
            'pack' => $pack,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    #[Route('/pack/{id}/edit', name:'pack_edit', methods: ['GET', 'POST'])]
    public function editAction(Request $request, Pack $pack, EntityManagerInterface $entityManager): Response
    {
        $deleteForm = $this->createDeleteForm($pack);
        $editForm = $this->createForm(PackType::class, $pack);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('pack_edit', array('id' => $pack->getId()));
        }

        return $this->render('pack/edit.html.twig', array(
            'pack' => $pack,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    #[Route('/pack/{id}/delete', name:'pack_delete', methods: ['GET', 'DELETE'])]
    public function deleteAction(Request $request, Pack $pack, EntityManagerInterface $entityManager, FileManager $fileManager): Response
    {
        $form = $this->createDeleteForm($pack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($pack->getPictures() as $picture) {
                $fileManager->deletePicture($picture);
                $entityManager->remove($picture);
            }
            $entityManager->remove($pack);
            $entityManager->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'pack/delete.html.twig',
            ['pack' => $pack, 'form' => $this->createDeleteForm($pack)->createView()]
        );
    }

    private function createDeleteForm(Pack $pack): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('pack_delete', array('id' => $pack->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}

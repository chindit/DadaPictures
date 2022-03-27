<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BannedPicture;
use App\Entity\Pack;
use App\Form\Type\PackType;
use App\Message\UploadMessage;
use App\Message\ValidateUploadMessage;
use App\Model\Status;
use App\Repository\PackRepository;
use App\Service\FileManager;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('pack')]
class PackController extends AbstractController
{
    #[Route('/new', name:'pack_new', methods: ['GET', 'POST'])]
    public function newAction(
        Request $request,
        UploadManager $uploadManager,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        Security $security
    ): Response {
        $pack = new Pack();
        $form = $this->createForm(PackType::class, $pack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $pack->setStoragePath($uploadManager->moveUploadFilesToTempStorage($pack->getFiles()));
                $pack->setStatus(Status::PROCESSING_UPLOAD);
                $pack->setCreator($security->getUser());
                $entityManager->persist($pack);
                $entityManager->flush();

                $messageBus->dispatch(new UploadMessage($pack->getId()));

                $this->addFlash('success', $translator->trans('pack.created'));
                $this->addFlash('warning', $translator->trans('pack.validation'));
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Unable to handle file upload');
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('pack/new.html.twig', array(
            'pack' => $pack,
            'form' => $form->createView(),
        ));
    }

    #[Route('{pack}/ban', name: 'pack_ban', methods: ['GET'])]
    public function banAction(
        Pack $pack,
        EntityManagerInterface $entityManager,
        FileManager $fileManager,
        FlashBagInterface $flashBag,
    ): Response {
        foreach ($pack->getPictures() as $picture) {
            $bannedPicture = new BannedPicture($picture->getSha1sum());
            $entityManager->persist($bannedPicture);
            $entityManager->remove($picture);
            $fileManager->deletePicture($picture);
            $entityManager->flush();
        }

        $flashBag->add('info', 'Pack «' . $pack->getName() . '» has
            been correctly banned');

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/{id}/confirm', name:'pack_confirm', methods: ['GET'])]
    public function publishAction(Pack $pack, EntityManagerInterface $entityManager, MessageBusInterface $messageBus): Response
    {
        $pack->setStatus(Status::PROCESSING_VALIDATION);
        $entityManager->flush();
        $messageBus->dispatch(new ValidateUploadMessage($pack->getId()));

        return $this->redirectToRoute('homepage');
    }

    #[Route('/{id}', name: 'pack_show', methods: ['GET'])]
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

    #[Route('/{id}/edit', name:'pack_edit', methods: ['GET', 'POST'])]
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

    #[Route('/{id}/delete', name:'pack_delete', methods: ['GET', 'DELETE'])]
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

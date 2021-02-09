<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\BannedPicture;
use App\Entity\Pack;
use App\Form\Type\PreShowType;
use App\Form\Type\PackType;
use App\Message\PackMessage;
use App\Model\Status;
use App\Repository\PackRepository;
use App\Service\FileManager;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('pack')]
class PackController extends AbstractController
{
    #[Route('/', name:'pack_index', methods: ['GET'])]
    public function indexAction(
        PackRepository $packRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response
    {
        $pagination = $paginator->paginate(
            $packRepository->findBy(['status' => Status::OK], ['id' => 'desc']),
            (int)$request->query->get('page', 1),
            25
        );

        return $this->render('pack/index.html.twig', array(
            'packs' => $pagination,
        ));
    }

    #[Route('/new', name:'pack_new', methods: ['GET', 'POST'])]
    public function newAction(
        Request $request,
        UploadManager $uploadManager,
        TranslatorInterface $translator
    ): Response
    {
        $pack = new Pack();
        $form = $this->createForm(PackType::class, $pack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $uploadManager->upload($pack);
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
    public function banAction(Pack $pack, EntityManagerInterface $entityManager, FileManager $fileManager): Response
    {
        foreach ($pack->getPictures() as $picture) {
            $bannedPicture = new BannedPicture($picture->getSha1sum());
            $entityManager->persist($bannedPicture);
            $entityManager->remove($picture);
            $fileManager->deletePicture($picture);
            $entityManager->flush();
        }

        $this->get('session')->getFlashBag()->add('info', 'Pack «' . $pack->getName() . '» has
            been correctly banned');

        return $this->redirectToRoute('admin_dashboard');
    }

    /**
     * Display result of pack upload
     *
     * @param Pack $pack
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @internal param array $files
     *
     * @Route("/uploadConfirm/{id}", name="pack_pre_show", methods={"GET", "POST"})
     */
    public function preShowAction(Pack $pack, Request $request): Response
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
     * @Route("/{id}", name="pack_show", methods={"GET"})
     */
    public function showAction(Pack $pack): Response
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
     * @Route("/{id}/edit", name="pack_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Pack $pack): Response
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

    #[Route('/{id}/delete', name:'pack_delete', methods: ['GET', 'DELETE'])]
    public function deleteAction(Request $request, Pack $pack, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createDeleteForm($pack);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->remove($pack);
            $entityManager->flush();

            return $this->redirectToRoute('pack_index');
        }

        return $this->render('pack/delete.html.twig', ['pack' => $pack, 'form' => $this->createDeleteForm($pack)->createView()]);
    }

    /**
     * Creates a form to delete a pack entity.
     */
    private function createDeleteForm(Pack $pack): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('pack_delete', array('id' => $pack->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}

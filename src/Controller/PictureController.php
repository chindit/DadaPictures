<?php
declare(strict_types=1);

namespace App\Controller;


use App\Entity\BannedPicture;
use App\Entity\Pack;
use App\Entity\Picture;
use App\Entity\Tag;
use App\Form\Type\PictureTagType;
use App\Repository\PictureRepository;
use App\Service\FileManager;
use App\Service\Path;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('picture')]
class PictureController extends AbstractController
{
    #[Route('/pack/{id}', name: 'pack_view_pictures', methods: ['GET'])]
    public function viewPackPicturesAction(Pack $pack): Response
    {
        return $this->render('picture/diaporama.html.twig', ['pack' => $pack]);
    }

    #[Route('/tag/random', name: 'picture_tag', methods: ['GET'])]
    public function pictureAddTagsAction(PictureRepository $pictureRepository, FlashBagInterface $flashBag): Response
    {
        if (!$picture = $pictureRepository->getPictureWithoutTags()) {
            $flashBag->add('info', 'All pictures are tagged');

            return $this->redirectToRoute('pack_index');
        }

        $form = $this->createForm(PictureTagType::class, $picture, ['action' => $this->generateUrl('pictures_add_tag', ['id' => $picture->getId()])]);

        return $this->render('picture/addTags.html.twig', ['picture' => $picture, 'form' => $form->createView()]);
    }

    #[Route('/tag/{id}/add', name:'picture_add_tag', methods: ['POST'])]
    public function addTagsToPictureAction(
        Picture $picture,
        Request $request,
        EntityManagerInterface $entityManager,
        FlashBagInterface $flashBag
    ): Response
    {
        $form = $this->createForm(PictureTagType::class, $picture);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
        } else {
            $flashBag->add('warning', 'An error has occurred during form submission');
        }

        return $this->forward('App:Picture:pictureAddTags');
    }

    #[Route('/tag/{id}/pictures', name: 'tag_pictures', defaults: ['id' => null], methods: ['GET'])]
    public function viewRandomPicturesByTagAction(Tag $tag, PictureRepository $pictureRepository): Response
    {
        $pictures = $pictureRepository->findRandomByTag($tag);

        return $this->render('picture/diaporama.html.twig', ['pictures' => $pictures, 'tag' => $tag]);
    }

    #[Route('/random', name: 'pictures_random', methods: ['GET'])]
    public function viewRandomAction(PictureRepository $pictureRepository): Response
    {
        $pictures = $pictureRepository->findRandom();

        return $this->render('picture/diaporama.html.twig', ['pictures' => $pictures]);
    }

    #[Route('{picture}/ban', name:'picture_ban', methods: ['GET'])]
    public function banAction(
        Picture $picture,
        EntityManagerInterface $entityManager,
        FileManager $fileManager,
        FlashBagInterface $flashBag
    ): Response
    {
        $bannedPicture = new BannedPicture($picture->getSha1sum());
        $entityManager->persist($bannedPicture);
        $entityManager->remove($picture);
        $fileManager->deletePicture($picture);
        $entityManager->flush();

        $flashBag->add('info', 'File «' . $picture->getFilename() . '» has
            been correctly banned');

        return $this->redirectToRoute('admin_dashboard');
    }

    /**
     * Deletes a pack entity.
     */
    #[Route('{picture}/delete', name:'picture_delete', methods: ['GET', 'DELETE'])]
    public function deleteAction(
        Request $request,
        Picture $picture,
        EntityManagerInterface $entityManager,
        FileManager $fileManager,
        FlashBagInterface $flashBag
    ): Response
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('picture_delete', array('picture' => $picture->getId())))
            ->setMethod('DELETE')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->remove($picture);

            $fileManager->deletePicture($picture);
            $entityManager->flush();

            $flashBag->add('info', 'File «' . $picture->getFilename() . '» has been correctly removed');

            return $this->redirectToRoute('pack_index');
        }

        return $this->render('picture/delete.html.twig', ['picture' => $picture, 'form' => $form->createView()]);
    }

    #[Route('/view/{picture}', name:'view_picture', methods: ['GET'])]
    public function viewPicture(Picture $picture, Path $path): Response
    {
        return new Response(
            file_get_contents($path->getPictureFullpath($picture)) ?: '',
            Response::HTTP_OK,
            ['Content-Type' => $picture->getMime()]
        );
    }
}

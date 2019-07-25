<?php
declare(strict_types=1);

namespace App\Controller;


use App\Entity\Pack;
use App\Entity\Picture;
use App\Entity\Tag;
use App\Form\Type\PictureTagType;
use App\Service\FileManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PictureController
 * @package App\Controller
 *
 * @Route("pictures")
 */
class PictureController extends AbstractController
{

    /**
     * Render JS for pictures in pack
     * @param Pack $pack
     * @return Response
     *
     * @Route("/pack/{id}", name="pack_view_pictures", methods={"GET"})
     */
    public function viewPackPicturesAction(Pack $pack): Response
    {
        return $this->render('picture/diaporama.html.twig', ['pack' => $pack]);
    }

    /**
     * Add tags for a random picture
     * @return Response
     *
     * @Route("/tag/random", name="pictures_tag", methods={"GET"})
     */
    public function pictureAddTagsAction(): Response
    {
        if (!$picture = $this->getDoctrine()->getRepository(Picture::class)->getPictureWithoutTags()) {
            $this->get('session')->getFlashBag()->add('info', 'All pictures are tagged');

            return $this->redirectToRoute('pack_index');
        }

        $form = $this->createForm(PictureTagType::class, $picture, ['action' => $this->generateUrl('pictures_add_tag', ['id' => $picture->getId()])]);

        return $this->render('picture/addTags.html.twig', ['picture' => $picture, 'form' => $form->createView()]);
    }

    /**
     * @param Picture $picture
     * @param Request $request
     * @return Response
     *
     * @Route("/tag/{id}/add", name="pictures_add_tag", methods={"POST"})
     */
    public function addTagsToPictureAction(Picture $picture, Request $request): Response
    {
        $form = $this->createForm(PictureTagType::class, $picture);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
        } else {
            $this->get('session')->getFlashBag()->add('warning', 'An error has occurred during form submission');
        }

        return $this->forward('App:Picture:pictureAddTags');
    }

    /**
     * View pictures ordered randomly by tag
     * @param Tag $tag
     * @return Response
     *
     * @Route("/tag/{id}/pictures", name="tag_pictures", defaults={"id" = null}, methods={"GET"})
     */
    public function viewRandomPicturesByTagAction(Tag $tag): Response
    {
        $pictures = $this->getDoctrine()->getRepository(Picture::class)->findRandomByTag($tag);

        return $this->render('picture/diaporama.html.twig', ['pictures' => $pictures, 'tag' => $tag]);
    }

    /**
     * Return 50 random pictures
     * @return Response
     *
     * @Route("/random", name="pictures_random", methods={"GET"})
     */
    public function viewRandomAction(): Response
    {
        $pictures = $this->getDoctrine()->getRepository(Picture::class)->findRandom();

        return $this->render('picture/diaporama.html.twig', ['pictures' => $pictures]);
    }

    /**
     * Deletes a pack entity.
     *
     * @Route("/{id}/delete", name="picture_delete", methods={"GET", "DELETE"})
     * @param Request $request
     * @param Pack|Picture $picture
     * @return Response
     */
    public function deleteAction(Request $request, Picture $picture): Response
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('picture_delete', array('id' => $picture->getId())))
            ->setMethod('DELETE')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($picture);

            $this->get(FileManager::class)->deletePicture($picture);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', 'File «' . $picture->getFilename() . '» has
            been correctly removed');

            return $this->redirectToRoute('pack_index');
        }

        return $this->render('picture/delete.html.twig', ['picture' => $picture, 'form' => $form->createView()]);
    }
}

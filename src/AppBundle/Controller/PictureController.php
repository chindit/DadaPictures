<?php
declare(strict_types=1);

namespace AppBundle\Controller;


use AppBundle\Entity\Pack;
use AppBundle\Entity\Picture;
use AppBundle\Entity\Tag;
use AppBundle\Form\Type\PictureTagType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PictureController
 * @package AppBundle\Controller
 *
 * @Route("pictures")
 */
class PictureController extends Controller
{

    /**
     * Render JS for pictures in pack
     * @param Pack $pack
     * @return Response
     *
     * @Route("/pack/{id}", name="pack_view_pictures")
     * @Method("GET")
     */
    public function viewPackPicturesAction(Pack $pack): Response
    {
        return $this->render('picture/diaporama.html.twig', ['pack' => $pack]);
    }

    /**
     * Add tags for a random picture
     * @return Response
     *
     * @Route("/tag/random", name="pictures_tag")
     * @Method("GET")
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
     * @Route("/tag/{id}/add", name="pictures_add_tag")
     * @Method("POST")
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

        return $this->forward('AppBundle:Picture:pictureAddTags');
    }

    /**
     * View pictures ordered randomly by tag
     * @param Tag $tag
     * @return Response
     *
     * @Route("/tag/{id}/pictures", name="tag_pictures", defaults={"id" = null})
     * @Method("GET")
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
     * @Route("/random", name="pictures_random")
     */
    public function viewRandomAction(): Response
    {
        $pictures = $this->getDoctrine()->getRepository(Picture::class)->findRandom();

        return $this->render('picture/diaporama.html.twig', ['pictures' => $pictures]);
    }
}

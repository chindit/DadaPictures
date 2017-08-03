<?php
declare(strict_types=1);

namespace AppBundle\Controller;


use AppBundle\Entity\Pack;
use AppBundle\Entity\Picture;
use AppBundle\Form\Type\PictureTagType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PictureController extends Controller
{

    /**
     * Render JS for pictures in pack
     * @param Pack $pack
     * @return Response
     *
     * @Route("/pictures/pack/{id}", name="pack_view_pictures")
     * @Method("GET")
     */
    public function viewPackPicturesAction(Pack $pack) : Response
    {
        return $this->render('picture/diaporama.html.twig', ['pack' => $pack]);
    }

    /**
     * Add tags for a random picture
     * @param Pack $pack
     * @return Response
     *
     * @Route("/pictures/tag/{id}", name="pictures_tag", defaults={"id" = null})
     * @Method({"GET", "POST"})
     */
    public function pictureAddTagesAction(Picture $picture = null, Request $request) : Response
    {
        if (!$picture) {
            $picture = $this->getDoctrine()->getRepository(Picture::class)->getPictureWithoutTags();
        }
        $form = $this->createForm(PictureTagType::class, $picture);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $picture = $this->getDoctrine()->getRepository(Picture::class)->getPictureWithoutTags();
        }

        return $this->render('picture/addTags.html.twig', ['picture' => $picture, 'form' => $form->createView()]);
    }
}

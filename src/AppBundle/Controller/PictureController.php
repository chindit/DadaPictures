<?php
declare(strict_types=1);

namespace AppBundle\Controller;


use AppBundle\Entity\Pack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
}
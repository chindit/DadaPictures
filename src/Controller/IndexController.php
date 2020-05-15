<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function indexAction(): Response
    {
        return $this->render('default/index.html.twig');
    }

	/**
	 * @Route("/terms", name="terms_and_conditions", methods={"GET"})
	 */
    public function termsAndConditions(): Response
    {
    	return $this->render('default/terms.html.twig');
    }
}

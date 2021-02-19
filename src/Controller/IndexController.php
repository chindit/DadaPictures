<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Status;
use App\Repository\PackRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name:'homepage', methods:['GET'])]
    public function indexAction(
	    PackRepository $packRepository,
	    PaginatorInterface $paginator,
	    Request $request
    ): Response {
	    $pagination = $paginator->paginate(
		    $packRepository->findBy(['status' => Status::OK], ['id' => 'desc']),
		    (int)$request->query->get('page', '1'),
		    25
	    );

	    return $this->render('default/index.html.twig', array(
		    'packs' => $pagination,
	    ));
    }

    #[Route('/terms', name:'terms_and_conditions', methods:['GET'])]
    public function termsAndConditions(): Response
    {
        return $this->render('default/terms.html.twig');
    }
}

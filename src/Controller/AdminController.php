<?php
declare(strict_types=1);

namespace App\Controller;


use App\Repository\PackRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name:'admin_dashboard', methods:['GET'])]
    public function dashboard(PackRepository $packRepository): Response
    {
        return $this->render('admin/dashboard.html.twig',
            [
                'packs' => $packRepository->countPacksInValidation(),
            ]
        );
    }
}
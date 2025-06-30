<?php

namespace App\Controller;

use App\Repository\BandRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/band')]
final class BandController extends AbstractController
{
    #[Route('/', name: 'app_band', methods: ['GET'])]
    public function index(BandRepository $bandRepository): Response
    {
        return $this->render('band/index.html.twig', [
            'controller_name' => 'BandController',
            'bands' => $bandRepository->findAll(),
        ]);
    }

}

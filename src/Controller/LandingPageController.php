<?php

namespace App\Controller;

use App\Repository\BookingRepository;
use App\Repository\FestivalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LandingPageController extends AbstractController
{
    #[Route(name: 'app_booking_index', methods: ['GET'])]
    public function index(BookingRepository $bookingRepository, FestivalRepository $festivalRepository, Request $request): Response
    {
        $search = $request->query->get('search');
        $qb = $festivalRepository->findByFilters(
            $search,
            null,   // location
            null,   // startDate
            null,   // endDate
            null,   // sortField
            'ASC'   // sortDirection (default to ASC)
        );
        $hasSearch = $request->query->has('search');
        $queryp = 'false';
        if ($hasSearch) {
            $queryp = 'true';
        } else {
            $queryp = 'false';
        }
        $festivals = $qb->getQuery()->getResult();

        return $this->render('booking/home.html.twig', [
            'bookings' => $bookingRepository->findAll(),
            'festivals' => $festivals,
            'queryp' => $queryp,
        ]);
    }

}

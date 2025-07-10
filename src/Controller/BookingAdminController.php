<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/booking')]
final class BookingAdminController extends AbstractController
{
    #[Route(name: 'app_booking_index_admin', methods: ['GET'])]
    public function admin_index(Request $request, BookingRepository $bookingRepository, PaginatorInterface $paginator): Response
    {
        $festivalName = $request->get('festivalName');
        $email = $request->get('email');
        $fullName = $request->get('fullName');
        $sortField = $request->query->get('sortField', 'id');
        $sortDirection = $request->query->get('sortDirection', 'asc');

        $queryBuilder = $bookingRepository->findByFilters(
            $festivalName,
            $email,
            $fullName,
            $sortField,
            $sortDirection
        );

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            4 // Items per page
        );

        return $this->render('booking/index.html.twig', [
            'pagination' => $pagination,
            'filters' => [
                'festivalName' => $festivalName,
                'email' => $email,
                'fullName' => $fullName,
                'sortField' => $sortField,
                'sortDirection' => $sortDirection,
            ],
        ]);
    }

    #[Route('/new', name: 'app_booking_new_admin', methods: ['GET', 'POST'])]
    public function new_admin(Request $request, EntityManagerInterface $entityManager): Response
    {
        $booking = new Booking();

        $form = $this->createForm(BookingType::class, $booking, ['mode' => 'detailed']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($booking);
            $entityManager->flush();

            return $this->redirectToRoute('app_booking_index_admin', ['id' => $booking->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('/booking/new_booking_admin.html.twig', [
            'booking' => $booking,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_booking_show_admin', methods: ['GET'])]
    public function show(Booking $booking): Response
    {
        return $this->render('booking/show_admin.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_booking_edit_admin', methods: ['GET', 'POST'])]
    public function edit(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookingType::class, $booking, ['mode' => 'detailed']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_booking_index_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('booking/edit_admin.html.twig', [
            'booking' => $booking,
            'form' => $form,
        ]);
    }
}

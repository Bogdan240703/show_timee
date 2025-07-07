<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Festival;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use App\Repository\FestivalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookingController extends AbstractController
{
    #[Route('/booking', name: 'app_booking_index', methods: ['GET'])]
    public function index(BookingRepository $bookingRepository, FestivalRepository $festivalRepository): Response
    {
        return $this->render('booking/home.html.twig', [
            'bookings' => $bookingRepository->findAll(),
            'festivals' => $festivalRepository->findAll(),
        ]);
    }

    #[Route('/admin/booking', name: 'app_booking_index_admin', methods: ['GET'])]
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

    #[Route('/admin/booking/new', name: 'app_booking_new_admin', methods: ['GET', 'POST'])]
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

    #[Route('/admin/booking/{id}', name: 'app_booking_show_admin', methods: ['GET'])]
    public function show(Booking $booking): Response
    {
        return $this->render('booking/show_admin.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/admin/booking/{id}/edit', name: 'app_booking_edit_admin', methods: ['GET', 'POST'])]
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

    #[Route('/booking/new', name: 'app_booking_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $festivalId = $request->query->get('festivalId');

        $booking = new Booking();
        if ($festivalId) {
            $festival = $entityManager->getRepository(Festival::class)->find($festivalId);
            if ($festival) {
                $booking->setFestival($festival);
            }
        }
        $form = $this->createForm(BookingType::class, $booking, ['mode' => 'quick']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($booking);
            $entityManager->flush();

            return $this->redirectToRoute('app_booking_completed', ['id' => $booking->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('booking/make_booking.html.twig', [
            'booking' => $booking,
            'form' => $form,
        ]);
    }

    #[Route('/booking/completed/{id}', name: 'app_booking_completed', methods: ['GET'])]
    public function completed(Booking $booking): Response
    {
        return $this->render('booking/booking_confirmation.html.twig', ['booking' => $booking]);
    }

    #[Route('/booking/{id}', name: 'app_booking_delete', methods: ['POST'])]
    public function delete(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $booking->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($booking);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
    }
}

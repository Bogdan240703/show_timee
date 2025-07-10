<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Festival;
use App\Entity\User;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use App\Repository\FestivalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/booking')]
final class BookingController extends AbstractController
{
    

    #[Route('/tickets', name: 'app_tickets_page', methods: ['GET'])]
    public function tickets(BookingRepository $bookingRepository): Response
    {
        $user = $this->getUser();
        $bookings = $bookingRepository->findByUser($user);
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view your tickets.');
        }

        return $this->render('tickets/tickets_page.html.twig', [
            'bookings' => $bookings,
        ]);
    }

    #[Route('/ticket/{id}', name: 'app_ticket_show', methods: ['GET'])]
    public function showTicket(Booking $booking): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view your tickets.');
        }

        return $this->render('tickets/show.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/wishlist', name: 'app_wishlist_page', methods: ['GET'])]
    public function wishlist(BookingRepository $bookingRepository, FestivalRepository $festivalRepository): Response
    {
        return $this->render('booking/wishlist_page.html.twig', [
            'bookings' => $bookingRepository->findAll(),
            'festivals' => $festivalRepository->findAll(),
        ]);
    }

    #[Route('/wishlist/toggle/{id}', name: 'app_wishlist_toggle')]
    public function toggleWishlist(Festival $festival, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($user->getWishlist()->contains($festival)) {
            $user->removeFestivalFromWishlist($festival);
        } else {
            $user->addFestivalToWishlist($festival);
        }

        $em->flush();

        return $this->redirectToRoute('app_booking_index');
    }

    #[Route('/wishlist/page/toggle/{id}', name: 'app_wishlist_page_toggle')]
    public function toggleWishlistPage(Festival $festival, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        if ($user->getWishlist()->contains($festival)) {
            $user->removeFestivalFromWishlist($festival);
        } else {
            $user->addFestivalToWishlist($festival);
        }

        $em->flush();

        return $this->redirectToRoute('app_wishlist_page');
    }

    #[Route('/new', name: 'app_booking_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $festivalId = $request->query->get('festivalId');

        $booking = new Booking();
        if ($festivalId) {
            $festival = $entityManager->getRepository(Festival::class)->find($festivalId);
            if ($festival) {
                $booking->setFestival($festival);
            }
        }
        $user = $security->getUser();
        if ($user) {
            $booking->setUser($user);
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

    #[Route('/completed/{id}', name: 'app_booking_completed', methods: ['GET'])]
    public function completed(Booking $booking): Response
    {
        return $this->render('booking/booking_confirmation.html.twig', ['booking' => $booking]);
    }


    #[Route('/{id}', name: 'app_booking_delete', methods: ['POST'])]
    public function delete(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $booking->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($booking);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
    }
}

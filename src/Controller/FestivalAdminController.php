<?php

namespace App\Controller;

use App\Entity\Festival;
use App\Form\FestivalType;
use App\Repository\BookingRepository;
use App\Repository\FestivalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/festival')]
final class FestivalAdminController extends AbstractController
{
    #[Route(name: 'app_festival_index', methods: ['GET'])]
    public function index(Request $request, FestivalRepository $festivalRepository, PaginatorInterface $paginator): Response
    {
        $name = $request->query->get('name');
        $location = $request->query->get('location');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
        $sortField = $request->query->get('sortField');
        $sortDirection = $request->query->get('sortDirection', 'asc');

        $queryBuilder = $festivalRepository->findByFilters($name, $location, $startDate, $endDate, $sortField, $sortDirection);

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            4 // Items per page
        );

        return $this->render('festival/index.html.twig', [
            'pagination' => $pagination,
            'filters' => [
                'name' => $name,
                'location' => $location,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'sortField' => $sortField,
                'sortDirection' => $sortDirection,
            ],
        ]);
    }

    #[Route('/new', name: 'app_festival_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $festival = new Festival();
        $form = $this->createForm(FestivalType::class, $festival);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($festival);
            $entityManager->flush();

            return $this->redirectToRoute('app_festival_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('festival/new.html.twig', [
            'festival' => $festival,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_festival_show', methods: ['GET'])]
    public function show(Festival $festival): Response
    {
        return $this->render('festival/show.html.twig', [
            'festival' => $festival,
        ]);
    }

    #[Route('/festival/dashboard/{id}', name: 'app_festival_dashboard', methods: ['GET', 'POST'])]
    public function dashboard(Request $request, EntityManagerInterface $entityManager, Festival $festival, BookingRepository $bookingRepository): Response
    {
        $nrOfTickets = $bookingRepository->countBookingsForFestivalId(festivalId: $festival->getId());
        $rawAges = $bookingRepository->getAgesPerBooking($festival->getId());
        $ages = array_map(fn($row) => $row['age'], $rawAges);

        $ageGroups = [0, 0, 0, 0, 0, 0];
        $nr = 0;
        foreach ($ages as $age) {
            ++$nr;
            if ($age >= 14 && $age < 18) {
                ++$ageGroups[0];
            } elseif ($age >= 18 && $age < 22) {
                ++$ageGroups[1];
            } elseif ($age >= 22 && $age < 26) {
                ++$ageGroups[2];
            } elseif ($age >= 26 && $age < 30) {
                ++$ageGroups[3];
            } elseif ($age >= 30 && $age < 40) {
                ++$ageGroups[4];
            } elseif ($age >= 40) {
                ++$ageGroups[5];
            }
        }
        for ($i = 0; $i < count($ageGroups); $i++) {
            if ($nr > 0) {
                $ageGroups[$i] = round(($ageGroups[$i] / $nr * 100), 1);
            } else {
                $ageGroups[$i] = 0;
            }
        }

        return $this->render('festival/dashboard.html.twig', [
            'festival' => $festival,
            'nrOfTickets' => $nrOfTickets,
            'ageGroups' => $ageGroups,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_festival_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Festival $festival, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FestivalType::class, $festival);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_festival_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('festival/edit.html.twig', [
            'festival' => $festival,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_festival_delete', methods: ['POST'])]
    public function delete(Request $request, Festival $festival, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $festival->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($festival);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_festival_index', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller;

use App\Entity\Band;
use App\Enum\MusicGenre;
use App\Form\BandType;
use App\Repository\BandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/band')]
final class BandController extends AbstractController
{
    #[Route(name: 'app_band_index', methods: ['GET'])]
    public function index(Request $request, BandRepository $bandRepository, PaginatorInterface $paginator): Response
    {
        $name = $request->query->get('name');
        $genre = $request->query->get('genre');
        $sortField = $request->query->get('sortField');
        $sortDirection = $request->query->get('sortDirection', 'asc');
        $queryBuilder = $bandRepository->findByFilters($name, $genre, $sortField, $sortDirection);
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            4 // Items per page
        );
        $allGenres = array_map(fn($enum) => $enum->value, MusicGenre::cases());
        return $this->render('band/index.html.twig', [
            'pagination' => $pagination,
            'filters' => [
                'name' => $name,
                'genre' => $genre,
                'sortField' => $sortField,
                'sortDirection' => $sortDirection,
            ],
            'allGenres' => $allGenres,
        ]);
    }

    #[Route('/new', name: 'app_band_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $band = new Band();
        $form = $this->createForm(BandType::class, $band);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('picture')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('band_pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $band->setPicture($newFilename);
            }
            $entityManager->persist($band);
            $entityManager->flush();

            return $this->redirectToRoute('app_band_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('band/new.html.twig', [
            'band' => $band,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_band_show', methods: ['GET'])]
    public function show(Band $band): Response
    {
        return $this->render('band/show.html.twig', [
            'band' => $band,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_band_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Band $band, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(BandType::class, $band);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('picture')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('band_pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $band->setPicture($newFilename);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_band_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('band/edit.html.twig', [
            'band' => $band,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_band_delete', methods: ['POST'])]
    public function delete(Request $request, Band $band, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $band->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($band);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_band_index', [], Response::HTTP_SEE_OTHER);
    }
}

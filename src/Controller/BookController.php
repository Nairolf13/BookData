<?php

namespace App\Controller;

use App\Entity\Book;
use App\GoogleBooks;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/book')]
final class BookController extends AbstractController
{
    #[Route('/', name: 'app_book_index', methods: ['GET', 'POST'])]
    public function index(Request $request, BookRepository $bookRepository, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFileName')->getData();

            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photo_dir'),
                        $newFilename
                    );
                    $book->setPhotoFileName($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image');
                    return $this->redirectToRoute('app_book_index');
                }
            }

            $entityManager->persist($book);
            $entityManager->flush();

            $this->addFlash('success', 'Le livre a été ajouté avec succès !');
            return $this->redirectToRoute('app_book_index');
        }

        return $this->render('book/index.html.twig', [
            'books' => $bookRepository->findAll(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/get', name: 'app_book_get', methods: ['GET'])]
    public function getBook(Book $book): JsonResponse
    {
        return $this->json([
            'title' => $book->getTitle(),
            'author' => $book->getAuthor(),
            'publicationDate' => $book->getPublicationDate()->format('Y-m-d'),
            'ISBN' => $book->getISBN(),
            'buyBy' => $book->getBuyBy(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_book_edit', methods: ['POST'])]
    public function edit(Request $request, Book $book, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $oldPhotoFileName = $book->getPhotoFileName();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFileName')->getData();

            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    // Supprimer l'ancienne photo si elle existe
                    if ($oldPhotoFileName) {
                        $oldPhotoPath = $this->getParameter('photo_dir') . '/' . $oldPhotoFileName;
                        if (file_exists($oldPhotoPath)) {
                            unlink($oldPhotoPath);
                        }
                    }

                    $photoFile->move(
                        $this->getParameter('photo_dir'),
                        $newFilename
                    );
                    $book->setPhotoFileName($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image');
                    return $this->redirectToRoute('app_book_index');
                }
            } else {
                // Si aucun nouveau fichier n'est uploadé, on garde l'ancien
                $book->setPhotoFileName($oldPhotoFileName);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Le livre a été modifié avec succès !');
        }

        return $this->redirectToRoute('app_book_index');
    }

    #[Route('/{id}/delete', name: 'app_book_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->request->get('_token'))) {
            if ($book->getPhotoFileName()) {
                $photoPath = $this->getParameter('photo_dir') . '/' . $book->getPhotoFileName();
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }
            
            $entityManager->remove($book);
            $entityManager->flush();
            $this->addFlash('success', 'Le livre a été supprimé avec succès !');
        }

        return $this->redirectToRoute('app_book_index');
    }

    public function __construct(private readonly GoogleBooks $googleBooks)
    {
    }

 

    #[Route('/search-book-info', name: 'app_book_search_info', methods: ['GET'])]
    public function searchBookInfo(Request $request, GoogleBooks $googleBooks, Security $security): JsonResponse
    {
        $query = $request->query->get('query', '');
        
        try {
            $results = $googleBooks->searchBooks($query, 1);
            
            if (!empty($results['items'])) {
                $book = $results['items'][0]['volumeInfo'];
                $user = $security->getUser();
                
                return $this->json([
                    'title' => $book['title'] ?? '',
                    'author' => $book['authors'][0] ?? '',
                    'publicationDate' => $book['publishedDate'] ?? '',
                    'ISBN' => $book['industryIdentifiers'][0]['identifier'] ?? '',
                ]);
            }
            
            return $this->json(['error' => 'Aucun livre trouvé'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la recherche'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

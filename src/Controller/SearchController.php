<?php


namespace App\Controller;


use App\Entity\Books;
use App\Entity\FavoriteBook;
use App\Repository\BooksRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    public function __construct(
        private SessionInterface $session,
        private BooksRepository $booksRepository,
    )
    { }

    /**
     * @Route("/search", name="search")
     */
    public function search(Request $request): Response
    {
        $query = $request->get('q');
        $user = $this->session->get('user');

        $notFoundMessage = sprintf('По вашему запросу "%s" ничего не найдено.', $query);

        if (empty($query)) {
            return $this->render(
                'search/search.html.twig',
                [
                    'message' => $notFoundMessage,
                    'user' => $user,
                ]
            );
        }

        $books = $this->searchBooks($query);

        if (empty($books)) {
            return $this->render(
                'search/search.html.twig',
                [
                    'message' => $notFoundMessage,
                    'user' => $user,
                ]
            );
        }

        $favoriteBooks = [];
        if (isset($user)) {
            $favoriteBooks = $this->getDoctrine()->getRepository(FavoriteBook::class)->getUserEntries($user['id_users']);
            /** @var FavoriteBook $entry */
            $favoriteBooks = array_map(function ($entry) {
                return $entry->getBookId();
            }, $favoriteBooks);
        }

        return $this->render(
            'search/search.html.twig',
            [
                'books' => $books,
                'user' => $user,
                'favoriteBooks' => $favoriteBooks,
            ]
        );
    }

    private function searchBooks(string $query): array {
        $qb = $this->booksRepository->createQueryBuilder('b');
        $qb
            ->join('b.author', 'a')
            ->join('b.publishingHouse', 'ph')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->like('a.nameAuthor', "'%$query%'"),
                    $qb->expr()->like('ph.namePublishingHouse', "'%$query%'"),
                    $qb->expr()->like('b.title', "'%$query%'")
                )
            );

        return $qb->getQuery()->getResult();
    }
}
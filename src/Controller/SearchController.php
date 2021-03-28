<?php


namespace App\Controller;


use App\Entity\Books;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController {
    /** @var SessionInterface $session */
    private $session;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    /**
     * @Route("/search", name="search")
     */
    public function search(Request $request): Response {
        $query = $request->get('q');
        $user = $this->session->get('user');

        $notFoundMessage = sprintf('По вашему запросу "%s" ничего не найдено.', $query);

        if (empty($query)) {
            return $this->render(
                'search/search.html.twig',
                array(
                    'message' => $notFoundMessage,
                    'user' => $user
                )
            );
        }

        /** @var Books[] $books */
        $books = $this->getDoctrine()->getRepository(Books::class)->searchBooks($query);

        if (empty($books)) {
            return $this->render(
                'search/search.html.twig',
                array(
                    'message' => $notFoundMessage,
                    'user' => $user
                )
            );
        }

        return $this->render(
            'search/search.html.twig',
            array(
                'books' => $books,
                'user' => $user
            )
        );
    }
}
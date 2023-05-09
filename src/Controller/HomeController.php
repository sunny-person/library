<?php


namespace App\Controller;


use App\Entity\Books;
use App\Entity\Category;
use App\Entity\FavoriteBook;
use App\Repository\BooksRepository;
use App\Repository\CategoryRepository;
use App\UI\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{

    private const BOOKS_PER_PAGE = 6;
    private BooksRepository $booksRepository;
    private CategoryRepository $categoryRepository;


    public function __construct(BooksRepository $booksRepository, CategoryRepository $categoryRepository)
    {
        $this->booksRepository = $booksRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Route("/", name="home")
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $requestCategory = $request->get('category');
        $page = (int)$request->get('page');
        if ($page <= 0) {
            $page = 1;
        }

        $category = null;
        if ((int)$requestCategory > 0) {
            $category = $this->categoryRepository->find($requestCategory);
        }
        $criteria = [];
        if ($category !== null) {
            $criteria = ['parent' => $category];
        }
        $booksCount = $this->booksRepository->count($criteria);
        $pagination = new Pagination($page, $booksCount, self::BOOKS_PER_PAGE);
        $books = $this->booksRepository->findBy(
            $criteria, limit: $pagination->getLimit(), offset: $pagination->getOffset()
        );

        $user = $request->getSession()->get('user');
        $favoriteBooks = [];
        if (isset($user)) {
            $favoriteBooks = $this->getDoctrine()->getRepository(FavoriteBook::class)->getUserEntries($user['id_users']);
            /** @var FavoriteBook $entry */
            $favoriteBooks = array_map(function ($entry) {
                return $entry->getBookId();
            }, $favoriteBooks);
        }

        $breadCrumbs = null;
        if (isset($requestCategory)) {
            $categoriesChain = $this->getDoctrine()->getRepository(Category::class)->getCategoriesChain($requestCategory);
            $breadCrumbs = $this->renderView(
                'ui/breadcrumbs.html.twig',
                [
                    'categories' => $categoriesChain,
                ]
            );
        }

        $pageNavigation = null;
        if ($booksCount > self::BOOKS_PER_PAGE) {
            $pageNavigation = $this->renderView(
                'ui/pagination.html.twig',
                [
                    'pagination' => [
                        'currentPage' => $pagination->getCurrentPage(),
                        'hasNextPage' => $pagination->hasNextPage(),
                        'lastPage' => $pagination->getTotalPageCount(),
                        'nextPage' => $pagination->getCurrentPage() + 1,
                        'prevPage' => $pagination->getCurrentPage() - 1,
                        'pageUrl' => '',
                    ],
                ]
            );
        }

        return $this->render(
            'home/index.html.twig',
            [
                'books' => $books,
                'user' => $user,
                'breadcrumbs' => $breadCrumbs,
                'pagination' => $pageNavigation,
                'favoriteBooks' => $favoriteBooks,
            ]
        );
    }

}
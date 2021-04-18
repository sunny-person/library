<?php


namespace App\Controller;


use App\Entity\Author;
use App\Entity\Books;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\FavoriteBook;
use App\Entity\PublishingHouse;
use App\Entity\TypePh;
use App\Repository\FavoriteBooksRepository;
use App\UI\Pagination;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


class BookController extends AbstractController {

    private const ADMIN_ROLE_ID = 1;
    private const BOOKS_PER_PAGE = 4;

    /** @var SessionInterface $session */
    private $session;

    private const UPLOAD_DIR = "upload_files/";

    private const ALLOWED_EXTENSIONS = array(
        'pdf'
    );

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    /**
     * @Route("/book/edit/{bookId}", methods={"GET"}, name="edit_get")
     * @param Request $request
     * @param int $bookId
     * @return Response
     */
    public function editGet(Request $request, int $bookId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $userRole = (int) $user['id_role'];
        if ($userRole !== self::ADMIN_ROLE_ID) {
            return new RedirectResponse('/');
        }

        $book = $this->getDoctrine()->getRepository(Books::class)->getBook($bookId);
        $authors = $this->getDoctrine()->getRepository(Author::class)->getAuthors();
        $cities = $this->getDoctrine()->getRepository(City::class)->getCities();
        $publishingHouses = $this->getDoctrine()->getRepository(PublishingHouse::class)->getPublishingHouses();
        $phTypes = $this->getDoctrine()->getRepository(TypePh::class)->getPhTypes();
        $categories = $this->getDoctrine()->getRepository(Category::class)->getCategories();

        return $this->render(
            'book/edit.html.twig',
            array(
                'book' => $book,
                'authors' => $authors,
                'cities' => $cities,
                'publishingHouses' => $publishingHouses,
                'typePhs' => $phTypes,
                'categories' => $categories,
                'errorMessage' => $request->get('errorMessage')
            )
        );
    }

    /**
     * @Route("/book/edit/{bookId}", methods={"POST"})
     * @param Request $request
     * @param int $bookId
     * @return Response
     */
    public function editPost(Request $request, int $bookId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $userRole = (int) $user['id_role'];
        if ($userRole !== self::ADMIN_ROLE_ID) {
            return new RedirectResponse('/');
        }

        /** @var Books $book */
        $book = $this->getDoctrine()->getRepository(Books::class)->getBook($bookId);
        if (!isset($book)) {
            return new RedirectResponse('/');
        }

        $del = $request->get('del');
        if (isset($del)) {
            $uploadFileName = $_SERVER['DOCUMENT_ROOT'] . self::UPLOAD_DIR . $book->getUrl();
            $result = unlink($uploadFileName);
            if (!$result) {
                return new RedirectResponse(
                    "/book/edit/${bookId}?errorMessage=Не%20удалось%20удалить%20файл"
                );
            }
            $this->getDoctrine()->getRepository(Books::class)->deleteBook($bookId);
            return new RedirectResponse('/');
        }

        $title = $request->get('title');
        $description = $request->get('description');
        $file = $request->files->get('URL');

        if ($file !== null){
            $url = $file->getClientOriginalName();
            $extension = pathinfo($url, PATHINFO_EXTENSION);
        } else {
            $book->setUrl($book->getUrl());
        }

        if (!empty($url)) {
            $error = false;
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . self::UPLOAD_DIR;
            if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                $error = true;
            } else if ($file->getError() !== UPLOAD_ERR_OK) {
                $error = true;
            } else if (file_exists($uploadDir . $url)) {
                $error = true;
            }

            if ($error) {
                return new RedirectResponse(
                    "/book/edit/${bookId}?errorMessage=Не%20удалось%20обновить%20книгу"
                );
            }

            $book->setUrl($url);
        }

        if (!empty($url)) {
            $uploadFileName = $_SERVER['DOCUMENT_ROOT'] . self::UPLOAD_DIR . $url;
            $moveResult = move_uploaded_file($file->getPathname(), $uploadFileName);
            if ($moveResult === false) {
                return new RedirectResponse(
                    "/book/edit/${bookId}?errorMessage=Не%20удалось%20обновить%20файл%20книги"
                );
            }
        }

        $page = $request->get('page');
        $year = $request->get('year');
        $authorId = $request->get('name_author');
        $cityId = $request->get('city');
        $phId = $request->get('ph');
        $phTypeId = $request->get('type_ph');
        $categoryId = $request->get('category');

        $book->setTitle($title);
        $book->setDescription($description);
        $book->setPage($page);
        $book->setYear($year);
        $book->getAuthor()->setIdAuthor($authorId);
        $book->getCity()->setIdCity($cityId);
        $book->getPublishingHouse()->setIdPublishingHouse($phId);
        $book->getTypePh()->setIdTypePh($phTypeId);
        $book->getParent()->setIdCategory($categoryId);

        $result = $this->getDoctrine()->getRepository(Books::class)->updateBook($book);

        if (!$request) {
            return new RedirectResponse(
                "/book/edit/${bookId}?errorMessage=Не%20удалось%20обновить%20книгу"
            );
        }

        return new RedirectResponse(
            "/book/edit/${bookId}?errorMessage=Книга%20успешно%20обновлена"
        );
    }

    /**
     * @Route("/book/add/", methods={"GET"}, name="add_get")
     * @param Request $request
     * @return Response
     */
    public function addGet(Request $request): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $userRole = (int) $user['id_role'];
        if ($userRole !== self::ADMIN_ROLE_ID) {
            return new RedirectResponse('/');
        }

        $authors = $this->getDoctrine()->getRepository(Author::class)->getAuthors();
        $cities = $this->getDoctrine()->getRepository(City::class)->getCities();
        $publishingHouses = $this->getDoctrine()->getRepository(PublishingHouse::class)->getPublishingHouses();
        $phTypes = $this->getDoctrine()->getRepository(TypePh::class)->getPhTypes();
        $categories = $this->getDoctrine()->getRepository(Category::class)->getCategories();

        return $this->render(
            'book/add.html.twig',
            array(
                'authors' => $authors,
                'cities' => $cities,
                'publishingHouses' => $publishingHouses,
                'typePhs' => $phTypes,
                'categories' => $categories,
                'errorMessage' => $request->get('errorMessage')
            )
        );
    }

    /**
     * @Route("/book/add/", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function addPost(Request $request): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $userRole = (int) $user['id_role'];
        if ($userRole !== self::ADMIN_ROLE_ID) {
            return new RedirectResponse('/');
        }

        $addBook = $request->get('add_book');
        if (isset($addBook)) {
            /** @var Books $book */
            $book = new Books();

            $title = $request->get('title');
            $description= $request->get('description');
            $file = $request->files->get('URL');

            if ($file !== null){
                $url = $file->getClientOriginalName();
                $extension = pathinfo($url, PATHINFO_EXTENSION);
            } else {
                return new RedirectResponse(
                    "/book/add?errorMessage=Добавьте%20файл%20книги"
                );
            }

            //Загрузка файлов не более 20M -- php.ini

            if (!empty($url)) {
                $error = false;
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . self::UPLOAD_DIR;
                if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                    $error = true;
                } else if ($file->getError() !== UPLOAD_ERR_OK) {
                    $error = true;
                } else if (file_exists($uploadDir . $url)) {
                    $error = true;
                }

                if ($error) {
                    return new RedirectResponse(
                        "/book/add?errorMessage=Не%20удалось%20загрузить%20документ%20книги"
                    );
                }

                $book->setUrl($url);
            }

            if (!empty($url)) {
                $uploadFileName = $_SERVER['DOCUMENT_ROOT'] . self::UPLOAD_DIR . $url;
                $moveResult = move_uploaded_file($file->getPathname(), $uploadFileName);
                if (!$moveResult) {
                    return new RedirectResponse(
                        "/book/add?errorMessage=Не%20удалось%20загрузить%20документ%20книги"
                    );
                }
            }

            $page = $request->get('page');
            $year = $request->get('year');
            $authorId = $request->get('name_author');
            $cityId = $request->get('city');
            $phId = $request->get('ph');
            $phTypeId = $request->get('type_ph');
            $categoryId = $request->get('category');

            $book->setTitle($title);
            $book->setDescription($description);
            $book->setPage($page);
            $book->setYear($year);
            $book->setAuthor($authorId);
            $book->setCity($cityId);
            $book->setPublishingHouse($phId);
            $book->setTypePh($phTypeId);
            $book->setParent($categoryId);

            $result = $this->getDoctrine()->getRepository(Books::class)->addBook($book);

            if (!$result) {
                return new RedirectResponse(
                    "/book/add?errorMessage=Не%20удалось%20добавить%20книгу"
                );
            }

            return new RedirectResponse(
                "/book/add?errorMessage=Книга%20успешно%20добавлена"
            );
        }

        $addPublishingHouse = $request->get('add_ph');
        if (isset($addPublishingHouse)) {
            $publishingHouse = new PublishingHouse();

            $name = $request->get('publishing_house');
            $publishingHouse->setNamePublishingHouse($name);
            $result=$this->getDoctrine()->getRepository(PublishingHouse::class)->addPublishingHouse($publishingHouse);

            if (!$result) {
                return new RedirectResponse(
                    "/book/add?errorMessage=Издательство%20не%20удалось%20добавить"
                );
            }

            return new RedirectResponse(
                "/book/add?errorMessage=Издательство%20успешно%20добавлено"
            );
        }

        $addCity = $request->get('add_city');
        if (isset($addCity)) {
            $city = new City();

            $name = $request->get('city');
            $city->setCity($name);
            $result=$this->getDoctrine()->getRepository(City::class)->addCity($city);

            if(!$result){
                return new RedirectResponse(
                    "/book/add?errorMessage=Город%20не%20удалось%20добавить"
                );
            }

            return new RedirectResponse(
                "/book/add?errorMessage=Город%20успешно%20добавлен"
            );
        }

        $addTypePh = $request->get('add_type_ph');
        if (isset($addTypePh)) {
            $typePh = new TypePh();
            $name = $request->get('type_ph');
            $typePh->setNameTypePh($name);
            $result=$this->getDoctrine()->getRepository(TypePh::class)->addTypePh($typePh);

            if(!$result) {
                return new RedirectResponse(
                    "/book/add?errorMessage=Тип%20издательсва%20успешно%20добавлен"
                );
            }
            return new RedirectResponse(
                "/book/add?errorMessage=Тип%20издательсва%20успешно%20добавлен"
            );
        }

        //Добавление категории и выбор только существующих родителей
        $addCategory=$request->get('add_category');
        if (isset($addCategory)) {
            $category = new Category();
            $nameCategory = $request->get('name_category');
            $idParent = $request->get('category');

            $category->setNameCategory($nameCategory);
            $category->setParent($idParent);

            $result=$this->getDoctrine()->getRepository(Category::class)->addCategory($category);

            if (!$result) {
                return new RedirectResponse(
                    "/book/add?errorMessage=Категорию%20не%20удалось%20добавить"
                );
            }
            return new RedirectResponse(
                "/book/add?errorMessage=Категория%20успешно%20добавлена"
            );
        }

        $addAuthor = $request->get('add_author');
        if (isset($addAuthor)) {
            $author = new Author();

            $name = $request->get('name_author');
            $author->setNameAuthor($name);
            $result = $this->getDoctrine()->getRepository(Author::class)->addAuthor($author);

            if (!$result) {
                return new RedirectResponse(
                    "/book/add?errorMessage=Автора%20не%20удалось%20добавить"
                );
            }

            return new RedirectResponse(
                "/book/add?errorMessage=Автор%20успешно%20добавлен"
            );
        }
    }

    /**
     * @Route("/book/favorites/add", methods={"POST"}, name="book_favorites_add")
     */
    public function addToFavorite(): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        try {
            /** @var FavoriteBooksRepository $fbr */
            $fbr = $this->getDoctrine()->getRepository(FavoriteBook::class);

            $request = file_get_contents('php://input');

            $fbData = json_decode($request, true);
            $bookId = $fbData['bookId'];
            $userId = $fbData['userId'];
            if (!isset($bookId) || !isset($userId)) {
                return new Response('Не был передан один из необходимых аргументов: book_id, user_id', 500);
            }

            $fb = new FavoriteBook();
            $fb->setUserId($userId);
            $fb->setBookId($bookId);

            $result = $fbr->addEntry($fb);

            if (!$result) {
                throw new RuntimeException();
            }

            return new JsonResponse(array('success' => true));
        } catch (Exception $e) {
            return new Response('При добавлении книги в список избранных произошла ошибка.', 500);
        }
    }

    /**
     * @Route("/book/favorites/remove", methods={"POST"}, name="book_favorites_remove")
     */
    public function removeToFavorite(): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        try {
            /** @var FavoriteBooksRepository $fbr */
            $fbr = $this->getDoctrine()->getRepository(FavoriteBook::class);

            $request = file_get_contents('php://input');

            $fbData = json_decode($request, true);
            $bookId = $fbData['bookId'];
            $userId = $fbData['userId'];
            if (!isset($bookId) || !isset($userId)) {
                return new Response('Не был передан один из необходимых аргументов: book_id, user_id', 500);
            }

            $fb = new FavoriteBook();
            $fb->setUserId($userId);
            $fb->setBookId($bookId);

            $result = $fbr->deleteEntry($fb);

            if (!$result) {
                throw new RuntimeException();
            }

            return new JsonResponse(array('success' => true));
        } catch (Exception $e) {
            return new Response('При удалении книги из списка избранных произошла ошибка.', 500);
        }
    }

    /**
     * @Route("/book_favorites", name="book_favorites")
     */
    public function getUserBooks(Request $request): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }
        $notFoundMessage = 'В список избранных книг ничего не добавлено!';

        $favoriteBooks = $this->getDoctrine()->getRepository(FavoriteBook::class)->getUserEntries($user['id_users']);

        if (empty($favoriteBooks)) {
            return $this->render(
                'book/favoritesbooks.html.twig',
                array(
                    'message' => $notFoundMessage,
                    'user' => $user
                )
            );
        }

        /** @var FavoriteBook $entry */
        $favoriteBooks = array_map(function ($entry) {
            return $entry->getBookId();
        }, $favoriteBooks);

        $page = (int) $request->get('page');
        if ($page <= 0) {
            $page = 1;
        }

        $booksRepository = $this->getDoctrine()->getRepository(Books::class);

        $booksCount = $booksRepository->getUserFavoriteBooksCount($user['id_users']);
        $pagination = new Pagination($page, $booksCount, self::BOOKS_PER_PAGE);

        $books = $booksRepository->getUserFavoriteBooks($user['id_users'], $pagination->getLimit(), $pagination->getOffset());

        $pageNavigation = null;
        if ($booksCount > self::BOOKS_PER_PAGE) {
            $pageNavigation = $this->renderView(
                'ui/pagination.html.twig',
                array(
                    'pagination' => array(
                        'currentPage' => $pagination->getCurrentPage(),
                        'hasNextPage' => $pagination->hasNextPage(),
                        'lastPage' => $pagination->getTotalPageCount(),
                        'nextPage' => $pagination->getCurrentPage() + 1,
                        'prevPage' => $pagination->getCurrentPage() - 1,
                        'pageUrl' => '/book_favorites'
                    )
                )
            );
        }


        return $this->render(
            'book/favoritesbooks.html.twig',
            array(
                'favorite_books' => $favoriteBooks,
                'books' => $books,
                'user' => $user,
                'pagination' => $pageNavigation
            )
        );
    }
}
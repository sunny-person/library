<?php


namespace App\Controller;


use App\Entity\Author;
use App\Entity\Books;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\FavoriteBook;
use App\Entity\PublishingHouse;
use App\Entity\TypePh;
use App\Repository\AuthorRepository;
use App\Repository\BooksRepository;
use App\Repository\CategoryRepository;
use App\Repository\CityRepository;
use App\Repository\FavoriteBooksRepository;
use App\Repository\PhTypesRepository;
use App\Repository\PublishingHouseRepository;
use App\UI\Pagination;
use Doctrine\Persistence\ManagerRegistry;
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
    private const BOOKS_PER_PAGE = 6;

    private const UPLOAD_DIR = "public/upload_files/";

    private const ALLOWED_EXTENSIONS = array(
        'pdf',
        'djvu'
    );

    public function __construct(
        private SessionInterface $session,
        private BooksRepository $booksRepository,
        private FavoriteBooksRepository $favoriteBooksRepository,
        private AuthorRepository $authorRepository,
        private PublishingHouseRepository $publishingHouseRepository,
        private CityRepository $cityRepository,
        private PhTypesRepository $phTypesRepository,
        private CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * @Route("/book/edit/{idBooks}", methods={"GET"}, name="edit_get")
     * @param Request $request
     * @param int $bookId
     * @return Response
     */
    public function editGet(Request $request, Books $book): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $userRole = (int) $user['id_role'];
        if ($userRole !== self::ADMIN_ROLE_ID) {
            return new RedirectResponse('/');
        }

        $authors = $this->authorRepository->findAll();
        $cities = $this->cityRepository->findAll();
        $publishingHouses = $this->publishingHouseRepository->findAll();
        $phTypes = $this->phTypesRepository->findAll();
        $categories = $this->categoryRepository->findAll();

        return $this->render(
            'book/edit.html.twig',
            array(
                'book' => $book,
                'authors' => $authors,
                'cities' => $cities,
                'publishingHouses' => $publishingHouses,
                'typePhs' => $phTypes,
                'categories' => $categories,
                'selectedAuthors' => array_map(fn ($a) => $a->getIdAuthor(), $book->getAuthor()->toArray()),
                'selectedPublishingHouses' => array_map(fn ($ph) => $ph->getIdPublishingHouse(),
                    $book->getPublishingHouse()->toArray()),
                'errorMessage' => $request->get('errorMessage')
            )
        );
    }

    /**
     * @Route("/book/edit/{idBooks}", methods={"POST"})
     * @param Request $request
     * @param int $bookId
     * @return Response
     */
    public function editPost(Request $request, Books $book, ManagerRegistry $doctrine): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $userRole = (int) $user['id_role'];
        if ($userRole !== self::ADMIN_ROLE_ID) {
            return new RedirectResponse('/');
        }

        $em = $doctrine->getManager();

        $del = $request->get('del');
        if (isset($del)) {
            $uploadFileName = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . self::UPLOAD_DIR . $book->getUrl();
            $result = false;
            if (file_exists($uploadFileName)) {
                $result = unlink($uploadFileName);
            }
            if (!$result) {
                return new RedirectResponse(
                    "/book/edit/{$book->getIdBooks()}?errorMessage=Не%20удалось%20удалить%20файл"
                );
            }

            try {
                $em->remove($book);
                $em->flush();
                return new RedirectResponse('/');
            } catch (Exception) {
                return new RedirectResponse(
                    "/book/edit/{$book->getIdBooks()}?errorMessage=Не%20удалось%20удалить%20информацию%20по%20книге!"
                );
            }
        }

        $title = $request->get('title');
        $description = $request->get('description');
        $file = $request->files->get('URL');

        if ($file !== null){
            $originalName = $file->getClientOriginalName();
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $newFileName = md5(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $extension;
        } else {
            $book->setUrl($book->getUrl());
        }

        if (!empty($newFileName)) {
            $error = false;
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . self::UPLOAD_DIR;
            if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                $error = true;
            } else if ($file->getError() !== UPLOAD_ERR_OK) {
                $error = true;
            } else if (file_exists($uploadDir . $newFileName)) {
                $error = true;
            }

            if ($error) {
                return new RedirectResponse(
                    "/book/edit/{$book->getIdBooks()}?errorMessage=Не%20удалось%20обновить%20книгу"
                );
            }

            $book->setUrl($newFileName);
        }

        if (!empty($newFileName)) {
            $uploadFileName = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . self::UPLOAD_DIR . $newFileName;
            $moveResult = move_uploaded_file($file->getPathname(), $uploadFileName);
            if ($moveResult === false) {
                return new RedirectResponse(
                    "/book/edit/{$book->getIdBooks()}?errorMessage=Не%20удалось%20обновить%20файл%20книги"
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

        $author = $this->authorRepository->findBy(['idAuthor' => $authorId]);
        $book->setAuthor($author);

        $city = $this->cityRepository->find($cityId);
        $book->setCity($city);

        $publishingHouse = $this->publishingHouseRepository->findBy(['idPublishingHouse' => $phId]);
        $book->setPublishingHouse($publishingHouse);

        $typePh = $this->phTypesRepository->find($phTypeId);
        $book->setTypePh($typePh);

        $category = $this->categoryRepository->find($categoryId);
        $book->setParent($category);

        try {
            $em->flush();
        } catch (Exception) {
            return new RedirectResponse(
                "/book/edit/{$book->getIdBooks()}?errorMessage=Не%20удалось%20обновить%20книгу"
            );
        }

        return new RedirectResponse(
            "/book/edit/{$book->getIdBooks()}?errorMessage=Книга%20успешно%20обновлена"
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
    public function addPost(Request $request, ManagerRegistry $doctrine): Response {
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
                $originalName = $file->getClientOriginalName();
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $newFileName = md5(pathinfo($originalName, PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $extension;
            } else {
                return new RedirectResponse(
                    "/book/add?errorMessage=Добавьте%20файл%20книги!"
                );
            }

            //Загрузка файлов не более 20M -- php.ini

            if (!empty($newFileName)) {
                $error = false;
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . self::UPLOAD_DIR;
                if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
                    $error = true;
                } else if ($file->getError() !== UPLOAD_ERR_OK) {
                    $error = true;
                } else if (file_exists($uploadDir . $newFileName)) {
                    $error = true;
                }

                if ($error) {
                    return new RedirectResponse(
                        "/book/add?errorMessage=Не%20удалось%20загрузить%20документ%20книги"
                    );
                }

                $book->setUrl($newFileName);
            }

            if (!empty($newFileName)) {
                $uploadFileName = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . self::UPLOAD_DIR . $newFileName;
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

            $book->setTitle($title);
            $book->setDescription($description);
            $book->setPage($page);
            $book->setYear($year);

            $authors = $this->authorRepository->findBy(['idAuthor' => $authorId]);
            $book->setAuthor($authors);

            $phId = $request->get('ph');
            $phs = $this->publishingHouseRepository->findBy(['idPublishingHouse' => $phId]);
            $book->setPublishingHouse($phs);

            $cityId = $request->get('city');
            $city = $this->cityRepository->find($cityId);
            $book->setCity($city);

            $phTypeId = $request->get('type_ph');
            $phType = $this->phTypesRepository->find($phTypeId);
            $book->setTypePh($phType);

            $categoryId = $request->get('category');
            $category = $this->categoryRepository->find($categoryId);
            $book->setParent($category);

            try {
                $em = $doctrine->getManager();
                $em->persist($book);
                $em->flush();
            } catch (Exception) {
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


        $favoriteBooks = $this->favoriteBooksRepository->findBy(['userId' => $user['id_users']]);

        if (empty($favoriteBooks)) {
            return $this->render(
                'book/favoritesbooks.html.twig',
                array(
                    'message' => $notFoundMessage,
                    'user' => $user
                )
            );
        }

        $favoriteBooks = array_map(function ($entry) {
            return $entry->getBookId();
        }, $favoriteBooks);

        $page = (int) $request->get('page');
        if ($page <= 0) {
            $page = 1;
        }

        $criteria = ['idBooks' => $favoriteBooks];
        $booksCount = $this->booksRepository->count($criteria);
        $pagination = new Pagination($page, $booksCount, self::BOOKS_PER_PAGE);
        $books = $this->booksRepository->findBy($criteria, limit: $pagination->getLimit(), offset: $pagination->getOffset());

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
                'books' => $books,
                'user' => $user,
                'pagination' => $pageNavigation,
                'favoriteBooks' => $favoriteBooks,
            )
        );
    }
}
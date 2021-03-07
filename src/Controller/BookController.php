<?php


namespace App\Controller;


use App\Entity\Author;
use App\Entity\Books;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\PublishingHouse;
use App\Entity\TypePh;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


class BookController extends AbstractController {

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

        $page = $request->get('page');
        $year = $request->get('year');
        $authorId = $request->get('name_author');
        $cityId = $request->get('city');
        $phId = $request->get('ph');
        $phTypeId = $request->get('type_ph');
        $categoryId = $request->get('category');

        $book->setTitle($title);
        $book->setPage($page);
        $book->setYear($year);
        $book->getAuthor()->setIdAuthor($authorId);
        $book->getCity()->setIdCity($cityId);
        $book->getPublishingHouse()->setIdPublishingHouse($phId);
        $book->getTypePh()->setIdTypePh($phTypeId);
        $book->getParent()->setIdCategory($categoryId);

        $result = $this->getDoctrine()->getRepository(Books::class)->updateBook($book);

        if ($result && !empty($url)) {
            $uploadFileName = $_SERVER['DOCUMENT_ROOT'] . self::UPLOAD_DIR . $url;
            move_uploaded_file($file->getPathname(), $uploadFileName);
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

        $addBook = $request->get('add_book');
        if (isset($addBook)) {
            /** @var Books $book */
            $book = new Books();

            $title = $request->get('title');
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

            $page = $request->get('page');
            $year = $request->get('year');
            $authorId = $request->get('name_author');
            $cityId = $request->get('city');
            $phId = $request->get('ph');
            $phTypeId = $request->get('type_ph');
            $categoryId = $request->get('category');

            $book->setTitle($title);
            $book->setPage($page);
            $book->setYear($year);
            $book->setAuthor($authorId);
            $book->setCity($cityId);
            $book->setPublishingHouse($phId);
            $book->setTypePh($phTypeId);
            $book->setParent($categoryId);

            $result = $this->getDoctrine()->getRepository(Books::class)->addBook($book);

            if ($result && !empty($url)) {
                $uploadFileName = $_SERVER['DOCUMENT_ROOT'] . self::UPLOAD_DIR . $url;
                move_uploaded_file($file->getPathname(), $uploadFileName);
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
}
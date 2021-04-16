<?php


namespace App\Controller;


use App\Entity\Author;
use App\Entity\Category;
use App\Entity\City;
use App\Entity\PublishingHouse;
use App\Entity\TypePh;
use App\Repository\AuthorRepository;
use App\Repository\CategoryRepository;
use App\Repository\CityRepository;
use App\Repository\PhTypesRepository;
use App\Repository\PublishingHouseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController {

    private const ADMIN_ROLE_ID = 1;

    /** @var SessionInterface $session */
    private $session;
    /** @var AuthorRepository $authorRepository */
    private $authorRepository;
    /** @var CategoryRepository $categoryRepository */
    private $categoryRepository;
    /** @var CityRepository $cityRepository */
    private $cityRepository;
    /** @var PhTypesRepository $phTypesRepository */
    private $phTypesRepository;
    /** @var PublishingHouseRepository $publishingHouseRepository */
    private $publishingHouseRepository;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    /**
     * @Route("/admin/", methods={"GET"}, name="admin_index")
     */
    public function index(): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $userRole = (int) $user['id_role'];
        if ($userRole !== self::ADMIN_ROLE_ID) {
            return new RedirectResponse('/');
        }

        $this->loadRepositories();

        $authors = $this->authorRepository->getAuthors();
        $categories = $this->categoryRepository->getCategories();
        $cities = $this->cityRepository->getCities();
        $phTypes = $this->phTypesRepository->getPhTypes();
        $publishingHouses = $this->publishingHouseRepository->getPublishingHouses();

        return $this->render(
            'admin/index.html.twig',
            array(
                'authors' => $authors,
                'categories' => $categories,
                'cities' => $cities,
                'phTypes' => $phTypes,
                'publishingHouses' => $publishingHouses
            )
        );
    }

    private function loadRepositories(): void {
        $this->authorRepository = $this->getDoctrine()->getRepository(Author::class);
        $this->categoryRepository = $this->getDoctrine()->getRepository(Category::class);
        $this->cityRepository = $this->getDoctrine()->getRepository(City::class);
        $this->phTypesRepository = $this->getDoctrine()->getRepository(TypePh::class);
        $this->publishingHouseRepository = $this->getDoctrine()->getRepository(PublishingHouse::class);
    }

}
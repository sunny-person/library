<?php


namespace App\Controller;


use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\Exceptions\CategoryRepositoryException;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController {

    /** @var SessionInterface $session */
    private $session;
    /** @var CategoryRepository $categoryRepository */
    private $categoryRepository;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    /**
     * @Route("/category/edit/{categoryId}", methods={"GET"}, name="category_edit")
     */
    public function editGet(int $categoryId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $category = $this->categoryRepository->getCategory($categoryId);
            $allCategories = $this->categoryRepository->getCategories();

            return $this->render(
                'category/edit.html.twig',
                array(
                    'category' => $category,
                    'allCategories' => $allCategories
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'category/edit.html.twig',
                array(
                    'error_message' => 'Категория не найдена!'
                )
            );
        }
    }

    /**
     * @Route("/category/edit/{categoryId}", methods={"POST"}, name="category_edit_post")
     */
    public function editPost(Request $request, int $categoryId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $category = $this->categoryRepository->getCategory($categoryId);

            $newName = $request->get('name_category');
            $parent = $request->get('parent');
            $category->setNameCategory($newName);
            $category->setParent($parent);
            $this->categoryRepository->updateCategory($category);

            return $this->render(
                'category/edit.html.twig',
                array(
                    'category' => $category,
                    'allCategories' => $this->categoryRepository->getCategories()
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'category/edit.html.twig',
                array(
                    'error_message' => 'Категория не найдена!'
                )
            );
        } catch (CategoryRepositoryException $e) {
            return $this->render(
                'category/edit.html.twig',
                array(
                    'category' => $category,
                    'allCategories' => $this->categoryRepository->getCategories(),
                    'error_message' => $e->getMessage()
                )
            );
        }
    }

    /**
     * @Route("/category/delete/{categoryId}", name="category_delete")
     */
    public function delete(int $categoryId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $category = $this->categoryRepository->getCategory($categoryId);

            $this->categoryRepository->deleteCategory($category);

            return $this->render(
                'category/edit.html.twig',
                array(
                    'error_message' => 'Категория успешно удалёна.'
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'category/edit.html.twig',
                array(
                    'error_message' => 'Категория не найдена!'
                )
            );
        } catch (CategoryRepositoryException $e) {
            return $this->render(
                'category/edit.html.twig',
                array(
                    'category' => $category,
                    'allCategories' => $this->categoryRepository->getCategories(),
                    'error_message' => $e->getMessage()
                )
            );
        }
    }

    private function loadRepositories() {
        $this->categoryRepository = $this->getDoctrine()->getRepository(Category::class);
    }

}
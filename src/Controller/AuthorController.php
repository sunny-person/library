<?php


namespace App\Controller;


use App\Entity\Author;
use App\Repository\AuthorRepository;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController {
    /** @var SessionInterface $session */
    private $session;
    /** @var AuthorRepository $authorRepository */
    private $authorRepository;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    /**
     * @Route("/author/edit/{authorId}", methods={"GET"}, name="author_edit")
     */
    public function editGet(int $authorId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $author = $this->authorRepository->getAuthor($authorId);

            return $this->render(
                'author/edit.html.twig',
                array(
                    'author' => $author
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'author/edit.html.twig',
                array(
                    'error_message' => 'Автор не найден!'
                )
            );
        }
    }

    /**
     * @Route("/author/edit/{authorId}", methods={"POST"}, name="author_edit_post")
     */
    public function editPost(Request $request, int $authorId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $author = $this->authorRepository->getAuthor($authorId);

            $newName = $request->get('name_author');
            $author->setNameAuthor($newName);
            $this->authorRepository->updateAuthor($author);

            return $this->render(
                'author/edit.html.twig',
                array(
                    'author' => $author
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'author/edit.html.twig',
                array(
                    'error_message' => 'Автор не найден!'
                )
            );
        } catch (Exception $e) {
            return $this->render(
                'author/edit.html.twig',
                array(
                    'author' => $author,
                    'error_message' => 'Не удалось обновить данные.'
                )
            );
        }
    }

    /**
     * @Route("/author/delete/{authorId}", name="author_delete")
     */
    public function delete(int $authorId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $author = $this->authorRepository->getAuthor($authorId);

            $this->authorRepository->deleteAuthor($author);

            return $this->render(
                'author/edit.html.twig',
                array(
                    'error_message' => 'Автор успешно удалён.'
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'author/edit.html.twig',
                array(
                    'error_message' => 'Автор не найден!'
                )
            );
        } catch (Exception $e) {
            return $this->render(
                'author/edit.html.twig',
                array(
                    'author' => $author,
                    'error_message' => 'Не удалось обновить данные.'
                )
            );
        }
    }

    private function loadRepositories() {
        $this->authorRepository = $this->getDoctrine()->getRepository(Author::class);
    }

}
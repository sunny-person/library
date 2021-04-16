<?php


namespace App\Controller;


use App\Entity\PublishingHouse;
use App\Repository\PublishingHouseRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use InvalidArgumentException;

class PublishingHouseController extends AbstractController {

    /** @var SessionInterface $session */
    private $session;
    /** @var PublishingHouseRepository $publishingHouseRepository */
    private $publishingHouseRepository;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    /**
     * @Route("/publishing_house/edit/{publishingHouseId}", methods={"GET"}, name="publishing_house_edit")
     */
    public function editGet(int $publishingHouseId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $publishing_house = $this->publishingHouseRepository->getPublishingHouse($publishingHouseId);

            return $this->render(
                'publishing_house/edit.html.twig',
                array(
                    'publishing_house' => $publishing_house
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'publishing_house/edit.html.twig',
                array(
                    'error_message' => 'Издательский дом не найден!'
                )
            );
        }
    }

    /**
     * @Route("/publishing_house/edit/{publishingHouseId}", methods={"POST"}, name="publishing_house_edit_post")
     */
    public function editPost(Request $request, int $publishingHouseId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $publishingHouse = $this->publishingHouseRepository->getPublishingHouse($publishingHouseId);

            $newName = $request->get('name_publishing_house');
            $publishingHouse->setNamePublishingHouse($newName);
            $this->publishingHouseRepository->updatePublishingHouse($publishingHouse);

            return $this->render(
                'publishing_house/edit.html.twig',
                array(
                    'publishing_house' => $publishingHouse
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'publishing_house/edit.html.twig',
                array(
                    'error_message' => 'Издательский дом не найден!'
                )
            );
        } catch (Exception $e) {
            return $this->render(
                'publishing_house/edit.html.twig',
                array(
                    'publishing_house' => $publishingHouse,
                    'error_message' => 'Не удалось обновить данные.'
                )
            );
        }
    }

    /**
     * @Route("/publishing_house/delete/{publishingHouseId}", name="publishing_house_delete")
     */
    public function delete(int $publishingHouseId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $publishingHouse = $this->publishingHouseRepository->getPublishingHouse($publishingHouseId);

            $this->publishingHouseRepository->deletePublishingHouse($publishingHouse);

            return $this->render(
                'publishing_house/edit.html.twig',
                array(
                    'error_message' => 'Издательский дом успешно удалён.'
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'publishing_house/edit.html.twig',
                array(
                    'error_message' => 'Издательский дом не найден!'
                )
            );
        } catch (Exception $e) {
            return $this->render(
                'publishing_house/edit.html.twig',
                array(
                    'publishing_house' => $publishingHouse,
                    'error_message' => 'Не удалось обновить данные.'
                )
            );
        }
    }

    private function loadRepositories() {
        $this->publishingHouseRepository = $this->getDoctrine()->getRepository(PublishingHouse::class);
    }
}
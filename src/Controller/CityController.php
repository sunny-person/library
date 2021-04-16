<?php


namespace App\Controller;


use App\Entity\City;
use App\Repository\CityRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use InvalidArgumentException;

class CityController extends AbstractController {

    /** @var SessionInterface $session */
    private $session;
    /** @var CityRepository $cityRepository */
    private $cityRepository;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    /**
     * @Route("/city/edit/{cityId}", methods={"GET"}, name="city_edit")
     */
    public function editGet(int $cityId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $city = $this->cityRepository->getCity($cityId);

            return $this->render(
                'city/edit.html.twig',
                array(
                    'city' => $city
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'city/edit.html.twig',
                array(
                    'error_message' => 'Город не найден!'
                )
            );
        }
    }

    /**
     * @Route("/city/edit/{cityId}", methods={"POST"}, name="city_edit_post")
     */
    public function editPost(Request $request, int $cityId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $city = $this->cityRepository->getCity($cityId);

            $newName = $request->get('city');
            $city->setCity($newName);
            $this->cityRepository->updateCity($city);

            return $this->render(
                'city/edit.html.twig',
                array(
                    'city' => $city
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'city/edit.html.twig',
                array(
                    'error_message' => 'Город не найден!'
                )
            );
        } catch (Exception $e) {
            return $this->render(
                'city/edit.html.twig',
                array(
                    'city' => $city,
                    'error_message' => 'Не удалось обновить данные.'
                )
            );
        }
    }

    /**
     * @Route("/city/delete/{cityId}", name="city_delete")
     */
    public function delete(int $cityId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $city = $this->cityRepository->getCity($cityId);

            $this->cityRepository->deleteCity($city);

            return $this->render(
                'city/edit.html.twig',
                array(
                    'error_message' => 'Город успешно удалён.'
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'city/edit.html.twig',
                array(
                    'error_message' => 'Город не найден!'
                )
            );
        } catch (Exception $e) {
            return $this->render(
                'city/edit.html.twig',
                array(
                    'city' => $city,
                    'error_message' => 'Не удалось обновить данные.'
                )
            );
        }
    }

    private function loadRepositories() {
        $this->cityRepository = $this->getDoctrine()->getRepository(City::class);
    }


}
<?php


namespace App\Controller;


use App\Entity\TypePh;
use App\Repository\PhTypesRepository;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class TypePhController extends AbstractController {
    /** @var SessionInterface $session */
    private $session;
    /** @var PhTypesRepository $phTypesRepository */
    private $phTypesRepository;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    /**
     * @Route("/type_ph/edit/{typePhId}", methods={"GET"}, name="type_ph_edit")
     */
    public function editGet(int $typePhId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $typePh = $this->phTypesRepository->getPHType($typePhId);

            return $this->render(
                'type_ph/edit.html.twig',
                array(
                    'type_ph' => $typePh
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'type_ph/edit.html.twig',
                array(
                    'error_message' => 'Тип издательсва не найден!'
                )
            );
        }
    }

    /**
     * @Route("/type_ph/edit/{typePhId}", methods={"POST"}, name="type_ph_edit_post")
     */
    public function editPost(Request $request, int $typePhId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $typePh = $this->phTypesRepository->getPHType($typePhId);

            $newName = $request->get('name_type_ph');
            $typePh->setNameTypePh($newName);
            $this->phTypesRepository->updateTypePh($typePh);

            return $this->render(
                'type_ph/edit.html.twig',
                array(
                    'type_ph' => $typePh
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'type_ph/edit.html.twig',
                array(
                    'error_message' => 'Издательский тип не найден!'
                )
            );
        } catch (Exception $e) {
            return $this->render(
                'type_ph/edit.html.twig',
                array(
                    'type_ph' => $typePh,
                    'error_message' => 'Не удалось обновить данные.'
                )
            );
        }
    }

    /**
     * @Route("/type_ph/delete/{typePhId}", name="type_ph_delete")
     */
    public function delete(int $typePhId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $this->loadRepositories();

        try {
            $typePh = $this->phTypesRepository->getPHType($typePhId);

            $this->phTypesRepository->deleteTypePh($typePh);

            return $this->render(
                'type_ph/edit.html.twig',
                array(
                    'error_message' => 'Тип документа успешно удалён.'
                )
            );
        } catch (InvalidArgumentException $e) {
            return $this->render(
                'type_ph/edit.html.twig',
                array(
                    'error_message' => 'Тип документа не найден!'
                )
            );
        } catch (Exception $e) {
            return $this->render(
                'type_ph/edit.html.twig',
                array(
                    'type_ph' => $typePh,
                    'error_message' => 'Не удалось обновить данные.'
                )
            );
        }
    }

    private function loadRepositories() {
        $this->phTypesRepository = $this->getDoctrine()->getRepository(TypePh::class);
    }

}
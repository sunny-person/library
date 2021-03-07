<?php


namespace App\Controller;


use App\Entity\Books;
use App\Entity\Information;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ReaderController extends AbstractController {
    /** @var SessionInterface $session */
    private $session;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    /**
     * @Route("/reader/{bookId}", name="reader")
     * @param Request $request
     * @param int $bookId
     * @return Response
     */
    public function reader(Request $request, int $bookId): Response{
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }

        $book=$this->getDoctrine()->getRepository(Books::class)->getBook($bookId);

        if(!isset($book)){
            return new RedirectResponse('/');
        }
        return $this->render(
            'reader/reader.html.twig',
            array(
                'user'=>$user,
                'book'=>$book
            )
        );
    }

    /**
     * @Route("/reader/{bookId}/get_user_information")
     *
     * @param int $bookId
     * @return Response
     */
    public function getUserInformation(int $bookId): Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse(
                '/auth/login'
            );
        }

        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (!is_array($data) || empty($data)) {
            return new JsonResponse(array('error' => 'Некорректный тип данных!'));
        }

        if (!isset($data['user']) || !isset($data['book'])) {
            return new JsonResponse(array('error' => 'Переданы пустые данные!'));
        }

        $userId = $data['user'];
        $bookId = $data['book'];

        $information=$this->getDoctrine()->getRepository(Information::class)->getInformation($userId, $bookId);

        if(!isset($information)){
            return new JsonResponse(array('page' => 1));
        }

        return new JsonResponse(
            array(
                'page'=>$information->getPage()
            )
        );

    }

    /**
     * @Route("/reader/{bookId}/save_user_information")
     * @param int $bookId
     * @return Response
     */
    public function saveUserInformation(int $bookId): Response{
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse(
                '/auth/login'
            );
        }

        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);

        if (!is_array($data) || empty($data)) {
            return new JsonResponse(array('error' => 'Некорректный тип данных!'));
        }

        if (!isset($data['user']) || !isset($data['book']) || !isset($data['page'])) {
            return new JsonResponse(array('error' => 'Переданы пустые данные!'));
        }

        $bookId = $data['book'];
        $userId = $data['user'];
        $page = $data['page'];
        $date = (new \DateTime('now'))->format('Y-m-d');

        $information=$this->getDoctrine()->getRepository(Information::class)->getInformation($userId, $bookId);

        if(!isset($information)){
            $information=new Information();
            $information->setUser($userId);
            $information->setBook($bookId);
            $information->setPage($page);
            $information->setDate($date);

            $result=$this->getDoctrine()->getRepository(Information::class)->addInformation($information);

            if(!$result){
                return new JsonResponse(
                    array(
                    'message'=>'Ошибка при добавлении информации'
                ));
            }

            return new JsonResponse(
                array(
                    'message'=>'Данные успешно добавлены'
                )
            );
        }

        $information->setPage($page);
        $information->setDate($date);

        $result=$this->getDoctrine()->getRepository(Information::class)->updateInformation($information);

        if(!$result){
            return new JsonResponse(
                array(
                    'message'=>'Ошибка при обновлении информации'
                ));
        }

        return new JsonResponse(
            array(
                'message'=>'Данные успешно обновлены'
            )
        );
    }
}
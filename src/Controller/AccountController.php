<?php


namespace App\Controller;


use App\Entity\Users;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;

class AccountController extends AbstractController
{

    /** @var SessionInterface $session */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/auth/sign", name="login")
     * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response
    {
        $user = $this->session->get('user');
        if (isset($user)) {
            return new RedirectResponse('/');
        }

        if ($request->isMethod('get')) {
            return $this->render(
                'auth/sign.html.twig'
            );
        }

        $error_fields = [];

        $login = $request->get('login');
        $password = $request->get('password');
        $captcha = $request->get('recaptcha_response');

        if (empty($login)) {
            $error_fields[] = 'login';
        }
        if (empty($password)) {
            $error_fields[] = 'password';
        }

        if (!empty($error_fields)) {
            $response = [
                "status" => false,
                "type" => 1,
                "message" => "Проверьте правильность полей",
                "fields" => $error_fields,
            ];

            return new JsonResponse($response);
        }
        $password = md5($password);

        /** @var null|Users $user */
        $user = $this->getDoctrine()->getRepository(Users::class)->getUser($login, $password);

        if (!isset($user) || $user->getLogin() != $login) {
            $response = [
                "status" => false,
                "message" => 'Неверный логин или пароль',
            ];

            return new JsonResponse(
                $response
            );
        }

        if (!empty($captcha)) {
            $secretKey = '';
            $reCaptchaValidationUrl = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captcha&remoteip=" . $_SERVER['REMOTE_ADDR'] . "");
            $result = json_decode($reCaptchaValidationUrl, true);
        }

        if (!$result['success']) {
            $response = [
                "status" => false,
                "message" => 'Код капчи не прошёл проверку на сервере!',
            ];

            return new JsonResponse($response);
        }

        $this->session->set('user', [ //массив сессии данных о пользователе
            "id_users" => $user->getIdUsers(),
            "full_name" => $user->getFullName(),
            "email" => $user->getEmail(),
            "id_role" => $user->getIdRole(),
        ]);

        $response = [
            "status" => true,
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/auth/register", name="register")
     * @param Request $request
     * @return Response
     */
    public function register(Request $request)
    {
        $user = $this->session->get('user');
        if (isset($user)) {
            return new RedirectResponse('/');
        }

        if ($request->isMethod('get')) {
            return $this->render(
                'auth/register.html.twig'
            );
        }

        $error_fields = [];
        $fullName = $request->get('full_name');
        $email = $request->get('email');
        $login = $request->get('login');
        $password = $request->get('password');
        $passwordConfirm = $request->get('password_confirm');
        $captcha = $request->get('recaptcha_response');

        if (empty($login)) {
            $error_fields[] = 'login';
        }
        if (empty($password)) {
            $error_fields[] = 'password';
        }
        if (empty($fullName) || !preg_match("/^[а-яА-Яa-zA-Z]+$/u", $fullName)) {
            $error_fields[] = 'full_name';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_fields[] = 'email';
        }
        if (empty($passwordConfirm)) {
            $error_fields[] = 'password_confirm';
        }

        if (!empty($error_fields)) {
            $response = [
                "status" => false,
                "type" => 1,
                "message" => "Проверьте правильность полей",
                "fields" => $error_fields,
            ];

            return new JsonResponse($response);
        }

        if ($password !== $passwordConfirm) {
            $response = [
                "status" => false,
                "message" => "Пароли не совпадают",
            ];

            return new JsonResponse($response);
        }

        if (!empty($captcha)) {
            $secretKey = '';
            $reCaptchaValidationUrl = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captcha&remoteip=" . $_SERVER['REMOTE_ADDR'] . "");
            $result = json_decode($reCaptchaValidationUrl, true);
        }

        if (!$result['success']) {
            $response = [
                "status" => false,
                "message" => 'Код капчи не прошёл проверку на сервере!',
            ];

            return new JsonResponse($response);
        }

        $password = md5($password);

        $result = $this->getDoctrine()->getRepository(Users::class)->addUser($fullName, $login, $email, $password);

        if (!$result) {
            $response = [
                "status" => false,
                "message" => 'Проверьте правильность полей',
            ];

            return new JsonResponse(
                $response
            );
        }

        $response = [
            "status" => true,
            "message" => "Регистрация прошла успешно",
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/auth/logout", name="logout")
     */
    public function logout()
    {
        $userId = $this->session->get('user');
        if (isset($userId)) {
            $this->session->remove('user');
        }

        return new RedirectResponse(
            '/'
        );
    }

}
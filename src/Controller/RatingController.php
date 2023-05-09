<?php


namespace App\Controller;


use App\Entity\Books;
use App\Entity\Rating;
use App\Entity\Users;
use App\Form\Type\RatingType;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class RatingController extends AbstractController {
    private SessionInterface $session;

    public function __construct(SessionInterface $session) {
        $this->session = $session;
    }

    /**
     * @Route("/book/{bookId}/ratings", name="rating_list")
     */
    public function list(int $bookId): Response {
        $user = $this->session->get('user');
        if (!is_null($response = $this->checkAuth())) {
            return $response;
        }
        $book = $this->getDoctrine()->getRepository(Books::class)->find($bookId);

        $ratingRepository = $this->getDoctrine()->getRepository(Rating::class);
        $currentUserReview = $ratingRepository->findOneBy(['user' => $user['id_users'], 'book' => $bookId]);
        if ($currentUserReview !== null) {
            $criteria = Criteria::create()
                    ->where(Criteria::expr()->neq('id', $currentUserReview->getId()))
                    ->andWhere(Criteria::expr()->eq('book', $book));
            $ratings = $this->getDoctrine()->getRepository(Rating::class)->matching($criteria)->toArray();
        } else {
            $ratings = $this->getDoctrine()->getRepository(Rating::class)->findBy(['book' => $bookId]);
        }

        return $this->render('book/rating/list.html.twig', ['ratings' => $ratings, 'currentUserReview' => $currentUserReview, 'book' => $book]);
    }

    /**
     * @Route("/book/{bookId}/rating", name="rating_new", methods={"GET"})
     */
    public function new(int $bookId): Response {
        if (!is_null($response = $this->checkAuth())) {
            return $response;
        }

        $book = $this->getDoctrine()->getRepository(Books::class)->find($bookId);
        if ($book === null) {
            return $this->redirectToRoute('home');
        }
        $user = $this->getDoctrine()->getRepository(Users::class)->find($this->session->get('user')['id_users']);

        $rating = new Rating();
        $rating->setBook($book);
        $rating->setUser($user);
        $form = $this->createForm(RatingType::class, $rating);

        return $this->render('book/rating/edit.html.twig', ['form' => $form->createView(), 'book' => $book]);
    }

    /**
     * @Route("/book/{bookId}/rating", name="rating_add", methods={"POST"})
     */
    public function add(Request $request, ManagerRegistry $managerRegistry, ValidatorInterface $validator, int $bookId): Response {
        if (!is_null($response = $this->checkAuth())) {
            return $response;
        }

        $book = $this->getDoctrine()->getRepository(Books::class)->find($bookId);
        if ($book === null) {
            return $this->redirectToRoute('home');
        }
        $user = $this->getDoctrine()->getRepository(Users::class)->find($this->session->get('user')['id_users']);

        $rating = new Rating();
        $rating->setUser($user);
        $rating->setBook($book);
        $form = $this->createForm(RatingType::class, $rating);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $rating = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($rating);
            $em->flush();

            return $this->redirectToRoute('rating_edit', ['bookId' => $bookId, 'ratingId' => $rating->getId()]);
        }

        return $this->render('book/rating/edit.html.twig', ['form' => $form->createView(), 'book']);
    }

    /**
     * @Route("/book/{bookId}/rating/{ratingId}", name="rating_show", methods={"GET"})
     */
    public function show(int $bookId, int $ratingId): Response {
        $rating = $this->getDoctrine()->getRepository(Rating::class)->findOneBy(['book' => $bookId, 'id' => $ratingId]);
        return $this->render('book/rating/show.html.twig', ['rating' => $rating]);
    }

    /**
     * @Route("/book/{bookId}/rating/{ratingId}/edit", name="rating_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, int $bookId, int $ratingId): Response {
        $rating = $this->getDoctrine()->getRepository(Rating::class)->findOneBy(['book' => $bookId, 'id' => $ratingId]);

        if ($rating === null) {
            return $this->redirectToRoute('home');
        }

        $user = $this->session->get('user');
        if (!is_null($response = $this->checkAuth())) {
            return $response;
        }
        if ($rating->getUser()->getIdUsers() !== $user['id_users']) {
            return $this->redirectToRoute('rating_show', ['bookId' => $bookId, 'ratingId' => $ratingId]);
        }

        $form = $this->createForm(RatingType::class, $rating);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $rating = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($rating);
            $em->flush();
        }

        return $this->render('book/rating/edit.html.twig', ['form' => $form->createView(), 'book' => $rating->getBook()]);
    }

    private function checkAuth(): ?Response {
        $user = $this->session->get('user');
        if (!isset($user)) {
            return new RedirectResponse('/auth/sign');
        }
        return null;
    }
}
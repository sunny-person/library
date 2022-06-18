<?php


namespace App\Form\Type;


use App\Entity\Rating;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;


class RatingType extends AbstractType {
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('user', HiddenType::class, [
                        'getter' => fn(Rating $rating, FormInterface $form): int => $rating->getUser()->getIdUsers(),
                        'setter' => function (Rating $rating, int $userId, FormInterface $form) {
                                $user = $this->entityManager->getRepository(Users::class)->find($userId);
                                $rating->setUser($user);
                        }
                ])
                ->add('id', HiddenType::class)
                ->add('comment', TextareaType::class, ['label' => 'Отзыв', 'required' => true])
                ->add('value', ChoiceType::class, [
                        'choices' => [
                                '1' => '1',
                                '2' => '2',
                                '3' => '3',
                                '4' => '4',
                                '5' => '5',
                        ],
                        'label' => 'Оценка',
                        'required' => true
                ])
                ->add('save', SubmitType::class, ['label' => 'Сохранить']);
    }
}
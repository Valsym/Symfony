<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'Введите действующий адрес',
                ],
            ])
//            ->add('email', EmailType::class)
            ->add('password', PasswordType::class, [
                'mapped' => false, // Не сохраняется в БД напрямую
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Пароль не может быть пустым']),
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'Пароль должен быть не короче {{ limit }} символов',
                    ]),
                ],
            ])
//            ->add('password', PasswordType::class)
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false, // Не сохраняется в БД
                'label' => 'Я согласен с условиями',
                'constraints' => [
                    new Assert\IsTrue(['message' => 'Вы должны согласиться с условиями']),
                ],
            ])
//            ->add('agreeTerms', CheckboxType::class, [
//                'label' => 'Я согласен с условиями',
//            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

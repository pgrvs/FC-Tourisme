<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class)
            ->add('nom', TextType::class)
            ->add('email', EmailType::class)
            ->add('pseudo', TextType::class, [
                'required' => false
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les champs de mot de passe doivent être identique.',
                'options' => [
                    'help' => "Le mot de passe doit contenir au moins 8 caractères.",
                    'label_attr' => [
                        'class' => 'text-primary'
                    ],
                    'attr' => [
                        'class' => 'password-field',
                        'placeholder' => 'Veuillez saisir votre mot de passe'
                    ]
                ],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répéter le mot de passe'],
            ])
            ->add('roles', ChoiceType::class, [
                'placeholder' => 'Choisissez une option',
                'choices'  => [
                    'Propriétaire d\'établissement' => "ROLE_RESTAURANT",
                    'Visiteur' => "ROLE_USER"
                ],
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
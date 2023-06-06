<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Etablissement;
use App\Entity\Ville;
use App\Repository\CategorieRepository;
use App\Repository\EtablissementRepository;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtablissementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('description', TextareaType::class, [
                'attr' => [
                    'rows' => 4
                ],
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'nom',
                'query_builder' => function(CategorieRepository $categorie) {
                    return $categorie   ->createQueryBuilder('c')
                                        ->orderBy('c.nom', 'ASC');
                }
            ])
            ->add('ville', EntityType::class, [
                    'class' => Ville::class,
                    'choice_label' => 'nom',
                    'query_builder' => function(VilleRepository $ville) {
                        return $ville   ->createQueryBuilder('v')
                                        ->orderBy('v.nom', 'ASC');
                    },
                    'placeholder' => 'Sélectionnez une ville'
                ]
            )
            ->add('adresse', TextType::class)
            ->add('numTelephone', TelType::class, [
                'attr' => [
                    'pattern' => '^(?:\d\s*){10}$',
                    'autocomplete' => 'off', // Désactive l'autocomplétion du navigateur
                ],
            ])
            ->add('email', EmailType::class)
            ->add('image', UrlType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Etablissement::class,
        ]);
    }
}
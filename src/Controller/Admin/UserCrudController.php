<?php

namespace App\Controller\Admin;

use App\Entity\Etablissement;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return User::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('email', 'Email')
                ->setRequired(true),
            ArrayField::new('roles', 'Roles'),
            TextField::new('password', 'Mot de passe')
                ->setRequired(true)
                ->hideOnForm(),
            TextField::new('prenom', 'Prénom')
                ->setRequired(true),
            TextField::new('nom', 'Nom')
                ->setRequired(true),
            TextField::new('pseudo', 'Pseudo')
                ->setRequired(false),
            DateTimeField::new('createdAt', 'Date de création')
                ->hideOnForm(),
            DateTimeField::new('updatedAt', 'Date de modification')
                ->hideOnForm(),
            AssociationField::new('favoris', 'Favoris')
                ->hideOnForm(),
            AssociationField::new('posseder', 'Établissements possédés')
                ->hideOnForm(),
        ];
    }

    // Redéfinir la méthode persistEntity qui va être appelée lors de la création
    // de l'article en base de données
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Vérifier que $entityInstance est bien une instance de la classe Etablissement
        if ( !$entityInstance instanceof Etablissement) return;
        $entityInstance->setCreatedAt(new \DateTime());
        $entityInstance->setUpdatedAt(new \DateTime());
        // Appel à la méthode héritée afin de persister l'entité
        parent::persistEntity($entityManager, $entityInstance);

    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setPageTitle(Crud::PAGE_INDEX, 'Liste des utilisateurs');
        $crud->setPageTitle(Crud::PAGE_DETAIL, "Détail d'un utilisateur");
        $crud->setPageTitle(Crud::PAGE_NEW, "Ajout d'un utilisateur");
        $crud->setPageTitle(Crud::PAGE_EDIT, "Modifié un utilisateur");
        $crud->setPaginatorPageSize(10);
        $crud->setDefaultSort(['createdAt' => 'DESC']);

        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->update(crud::PAGE_INDEX, Action::NEW,
            function (Action $action){
                $action ->setLabel("Ajouter utilisateur")
                    ->setIcon("fa fa-plus");
                return $action;
            }
        );

        $actions->update(crud::PAGE_NEW, Action::SAVE_AND_RETURN,
            function (Action $action){
                $action ->setLabel("Valider")
                    ->setIcon("fa fa-check");
                return $action;
            }
        );

        $actions->add(crud::PAGE_INDEX, Action::DETAIL);

        $actions->remove(crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);

        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters->add("nom")
            ->add("createdAt");
        return $filters;
    }
}

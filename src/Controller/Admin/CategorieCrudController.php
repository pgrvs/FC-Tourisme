<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use App\Entity\Etablissement;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategorieCrudController extends AbstractCrudController
{

    public static function getEntityFqcn(): string
    {
        return Categorie::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('nom')
                ->setLabel('Nom'),
            DateTimeField::new('createdAt')
                ->setLabel('Date de création')
                ->hideOnForm(),
            CollectionField::new('etablissements')
                ->setLabel('Établissements')
                ->hideOnForm()
        ];
    }

    // Redéfinir la méthode persistEntity qui va être appelée lors de la création
    // de l'article en base de données
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Vérifier que $entityInstance est bien une instance de la classe Etablissement
        if ( !$entityInstance instanceof Categorie) return;
        $entityInstance->setCreatedAt(new \DateTime());
        // Appel à la méthode héritée afin de persister l'entité
        parent::persistEntity($entityManager, $entityInstance);

    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setPageTitle(Crud::PAGE_INDEX, 'Liste des catégories');
        $crud->setPageTitle(Crud::PAGE_DETAIL, "Détail d'une catégorie");
        $crud->setPageTitle(Crud::PAGE_NEW, "Ajout d'une catégorie");
        $crud->setPageTitle(Crud::PAGE_EDIT, "Modifié une catégorie");
        $crud->setPaginatorPageSize(10);
        $crud->setDefaultSort(['createdAt' => 'DESC']);

        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->update(crud::PAGE_INDEX, Action::NEW,
            function (Action $action){
                $action ->setLabel("Ajouter catégorie")
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
        $filters->add("nom");
        return $filters;
    }


}

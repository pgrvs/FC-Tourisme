<?php

namespace App\Controller\Admin;

use App\Entity\Etablissement;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\String\Slugger\SluggerInterface;

class EtablissementCrudController extends AbstractCrudController
{
    private SluggerInterface $slugger;

    // Injecton du slugger au niveau du constructeur
    /**
     * @param SluggerInterface $slugger
     */
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public static function getEntityFqcn(): string
    {
        return Etablissement::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('nom')
                ->setLabel('Nom'),
            TextField::new('slug')
                ->hideOnForm()
                ->hideOnDetail()
                ->hideOnIndex(),
            TextEditorField::new('description')
                ->setLabel('Description')
                ->setSortable(false),
            TextField::new('numTelephone')
                ->setLabel('Numéro de téléphone'),
            TextField::new('adresse')
                ->setLabel('Adresse'),
            TextField::new('email')
                ->setLabel('Email'),
            ImageField::new('image')
                ->setLabel('Image')
                ->setBasePath('/uploads/images/etablissements')
                ->setUploadDir('public/uploads/images/etablissements') // Remplacez le chemin par le répertoire approprié
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false)
                ->hideOnIndex()
                ->hideOnForm()
                ->onlyOnDetail(),
            BooleanField::new('actif')
                ->setLabel('Actif'),
            BooleanField::new('accueil')
                ->setLabel('Accueil'),
            DateTimeField::new('createdAt')
                ->setLabel('Date de création')
                ->hideOnForm(),
            DateTimeField::new('updatedAt')
                ->setLabel('Date de mise à jour')
                ->hideOnForm(),
            AssociationField::new('categorie', 'Catégories'),
            AssociationField::new('ville', 'Ville'),
            AssociationField::new('favoris', 'Favoris')
                ->hideOnForm(),
            AssociationField::new('proprietaire', 'Proprietaire'),
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
        $entityInstance->setSlug($this->slugger->slug($entityInstance->getNom())->lower());
        // Appel à la méthode héritée afin de persister l'entité
        parent::persistEntity($entityManager, $entityInstance);

    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud->setPageTitle(Crud::PAGE_INDEX, 'Liste des établissements');
        $crud->setPageTitle(Crud::PAGE_DETAIL, "Détail d'un établissement");
        $crud->setPageTitle(Crud::PAGE_NEW, "Ajout d'un établissement");
        $crud->setPageTitle(Crud::PAGE_EDIT, "Modifié un établissement");
        $crud->setPaginatorPageSize(10);
        $crud->setDefaultSort(['createdAt' => 'DESC']);

        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->update(crud::PAGE_INDEX, Action::NEW,
            function (Action $action){
                $action ->setLabel("Ajouter établissement")
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

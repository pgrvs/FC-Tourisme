<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use App\Entity\Etablissement;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $url = $adminUrlGenerator
            ->setController(EtablissementCrudController::class)
            ->generateUrl();
        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration FC Tourisme');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToUrl('Quitter', 'fas fa-chevron-circle-left', $this->generateUrl('app_accueil'));
        // yield MenuItem::linkToCrud('Categorie', 'fas fa-list', CategorieCrudController::class);

        yield MenuItem::section("Etablissements");
        yield MenuItem::subMenu("Actions",'fas fa-bars')
            ->setSubItems(
                [ MenuItem::linkToCrud('Ajouter etablissement', 'fas fa-plus',Etablissement::class)
                    ->setAction(Crud::PAGE_NEW),
                MenuItem::linkToCrud('Lister etablissements', 'fas fa-eye',Etablissement::class)
                    ->setAction(Crud::PAGE_INDEX)
            ]);
        yield MenuItem::section("Catégories");
        yield MenuItem::subMenu("Actions",'fas fa-bars')
            ->setSubItems(
                [ MenuItem::linkToCrud('Ajouter categorie', 'fas fa-plus',Categorie::class)
                    ->setAction(Crud::PAGE_NEW),
                    MenuItem::linkToCrud('Lister categories', 'fas fa-eye',Categorie::class)
                        ->setAction(Crud::PAGE_INDEX)
                ]);
        yield MenuItem::section("Utilisateur");
        yield MenuItem::subMenu("Actions",'fas fa-bars')
            ->setSubItems(
                [ MenuItem::linkToCrud('Ajouter utilisateur', 'fas fa-plus',User::class)
                    ->setAction(Crud::PAGE_NEW),
                    MenuItem::linkToCrud('Lister utilisateurs', 'fas fa-eye',User::class)
                        ->setAction(Crud::PAGE_INDEX)
                ]);
    }
}

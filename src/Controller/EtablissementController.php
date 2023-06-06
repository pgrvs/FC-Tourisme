<?php

namespace App\Controller;

use App\Entity\Etablissement;
use App\Form\EtablissementType;
use App\Repository\CategorieRepository;
use App\Repository\EtablissementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

class EtablissementController extends AbstractController
{
    private EtablissementRepository $etablissementRepository;
    private SluggerInterface $slugger;
    private Security $security;

    /**
     * @param EtablissementRepository $etablissementRepository
     * @param SluggerInterface $slugger
     * @param Security $security
     */
    public function __construct(EtablissementRepository $etablissementRepository, SluggerInterface $slugger, Security $security)
    {
        $this->etablissementRepository = $etablissementRepository;
        $this->slugger = $slugger;
        $this->security = $security;
    }

    #[Route('/etablissements', name: 'app_etablissements')]
    public function getEtablissements(PaginatorInterface $paginator, Request $request): Response
    {
        $etablissements = $paginator->paginate(
            $this->etablissementRepository->findBy(["actif" => true], ['nom' => 'ASC']),
            $request->query->getInt('page', 1), /*page number*/
            12 /*limit per page*/
        );
        return $this->render('etablissement/index.html.twig', [
            'etablissements' => $etablissements,
        ]);
    }

    #[Route('/etablissements/{slug}', name: 'app_etablissements_slug')]
    public function getEtablissement($slug, Request $request): Response
    {
        $etablissement = $this->etablissementRepository->findOneBy(["slug" => $slug, "actif" => true]);

        return $this->render('etablissement/etablissement.html.twig', [
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('etablissements/nouveau', name: 'app_etablissements_nouveau', methods: ['GET', 'POST'], priority: 1)]
    public function newEtablissement(Request $request): Response
    {
        if (!$this->security->isGranted('ROLE_PROPRIETAIRE')) {
            return $this->redirectToRoute('app_accueil');
        }

        $etablissement = new Etablissement();

        // Création du formulaire
        $formEtablissement = $this->createForm(EtablissementType::class, $etablissement);

        // Reconnaitre si le formulaire a été soumis ou pas
        $formEtablissement->handleRequest($request);

        // Est-ce que le formulaire a été soumis
        if ($formEtablissement->isSubmitted() && $formEtablissement->isValid()){

            $etablissement
                ->setActif(true)
                ->setCreatedAt(new \DateTime())
                ->setAccueil(false)
                ->setSlug(($this->slugger->slug($etablissement->getNom())->lower()))
                ->setProprietaire($this->getUser())
            ;
            $this->getUser()->addPosseder($etablissement);

            // Insérer l'etablissement dans la base de données
            $this->etablissementRepository->add($etablissement, true);

            return $this->redirectToRoute('app_etablissements_proprietaire');
        }

        return $this->render('etablissement/nouveau.html.twig', [
            'formEtablissement' => $formEtablissement->createView(),
        ]);
    }

    #[Route('/etablissements/proprietaire', name: 'app_etablissements_proprietaire', priority: 1)]
    public function getEtablissementsProprietaire(PaginatorInterface $paginator, Request $request): Response
    {
        if (!$this->security->isGranted('ROLE_PROPRIETAIRE')) {
            return $this->redirectToRoute('app_accueil');
        }

        $etablissements = $paginator->paginate(
            $this->getUser()->getPosseder(),
            $request->query->getInt('page', 1), /*page number*/
            12 /*limit per page*/
        );
        return $this->render('etablissement/etablissementsPosseder.html.twig', [
            'etablissements' => $etablissements,
        ]);
    }

    #[Route('/etablissements/{slug}/proprietaire', name: 'app_etablissements_slug_proprietaire')]
    public function getEtablissementProprietaire($slug, Request $request): Response
    {
        if (!$this->security->isGranted('ROLE_PROPRIETAIRE')) {
            return $this->redirectToRoute('app_accueil');
        }

        $etablissement = $this->etablissementRepository->findOneBy(["slug" => $slug, "proprietaire" => $this->getUser()]);

        return $this->render('etablissement/etablissementPosseder.html.twig', [
            'etablissement' => $etablissement,
        ]);
    }

    #[Route('etablissements/{slug}/modifier', name: 'app_etablissements_modifier', methods: ['GET', 'POST'])]
    public function editEtablissement(Request $request, $slug): Response
    {
        if (!$this->security->isGranted('ROLE_PROPRIETAIRE')) {
            return $this->redirectToRoute('app_accueil');
        }

        $etablissement = $this->etablissementRepository->findOneBy(["slug" => $slug, "proprietaire" => $this->getUser()]);

        // Création du formulaire
        $formEtablissement = $this->createForm(EtablissementType::class, $etablissement);

        // Reconnaitre si le formulaire a été soumis ou pas
        $formEtablissement->handleRequest($request);

        // Est-ce que le formulaire a été soumis
        if ($formEtablissement->isSubmitted() && $formEtablissement->isValid()){

            $etablissement
                ->setUpdatedAt(new \DateTime())
                ->setSlug(($this->slugger->slug($etablissement->getNom())->lower()))
            ;
            // Insérer l'etablissement dans la base de données
            $this->etablissementRepository->add($etablissement, true);

            return $this->redirectToRoute('app_etablissements_proprietaire');
        }

        return $this->render('etablissement/edit.html.twig', [
            'formEtablissement' => $formEtablissement->createView(),
        ]);
    }

    #[Route('etablissements/{slug}/supprimer', name: 'app_etablissements_supprimer')]
    public function deleteEtablissement(Request $request, $slug, UserRepository $userRepository, CategorieRepository $categorieRepository, EntityManagerInterface $entityManager): Response
    {
        if (!$this->security->isGranted('ROLE_PROPRIETAIRE')) {
            return $this->redirectToRoute('app_accueil');
        }

        $etablissement = $this->etablissementRepository->findOneBy(["slug" => $slug, "proprietaire" => $this->getUser()]);

        $this->getUser()->removePosseder($etablissement);
        $entityManager->flush(); // Persister les changements

        foreach ($userRepository->findAll() as $user){
            $user->removeFavori($etablissement);
            $entityManager->flush(); // Persister les changements pour chaque utilisateur
        }

        foreach ($categorieRepository->findAll() as $categorie){
            $categorie->removeEtablissement($etablissement);
            $entityManager->flush(); // Persister les changements pour chaque catégorie
        }

        $this->etablissementRepository->remove($etablissement, true);

        return $this->redirectToRoute('app_etablissements_proprietaire');

    }
}

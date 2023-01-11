<?php

namespace App\Controller;

use App\Repository\EtablissementRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FavoriController extends AbstractController
{
    private EtablissementRepository $etablissementRepository;
    private EntityManagerInterface $entityManager;

    /**
     * @param EtablissementRepository $etablissementRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EtablissementRepository $etablissementRepository, EntityManagerInterface $entityManager)
    {
        $this->etablissementRepository = $etablissementRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/favoris', name: 'app_favori')]
    public function index(): Response
    {
        return $this->render('favori/index.html.twig', [
            'controller_name' => 'FavoriController',
        ]);
    }

    #[Route('/favoris/{slug}', name: 'app_add_favori_slug')]
    public function addFavori($slug): Response
    {
        $user = $this->getUser();
        $etablissemnt = $this->etablissementRepository->findOneBy(['slug' => $slug]);
        $user->addFavori($etablissemnt);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $this->redirectToRoute('app_etablissements');
    }

}

<?php

namespace App\Controller;

use App\Repository\EtablissementRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/favoris', name: 'app_favoris')]
    public function favoris(PaginatorInterface $paginator, Request $request): Response
    {
        $etablissements = $paginator->paginate(
            $this->getUser()->getFavoris(),
            $request->query->getInt('page', 1), /*page number*/
            12 /*limit per page*/
        );
        return $this->render('favori/index.html.twig', [
            'etablissements' => $etablissements,
        ]);
    }

    #[Route('/favoris/ajout/{slug}', name: 'app_add_favori_slug')]
    public function addFavori($slug): Response
    {
        $user = $this->getUser();
        $etablissemnt = $this->etablissementRepository->findOneBy(['slug' => $slug]);
        $user->addFavori($etablissemnt);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $this->redirectToRoute('app_etablissements');
    }

    #[Route('/favoris/supprime/{slug}', name: 'app_delete_favori_slug')]
    public function deleteFavori($slug): Response
    {
        $user = $this->getUser();
        $etablissemnt = $this->etablissementRepository->findOneBy(['slug' => $slug]);
        $user->removeFavori($etablissemnt);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $this->redirectToRoute('app_etablissements');
    }
}

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
use Symfony\Component\Security\Core\Security;

class FavoriController extends AbstractController
{
    private EtablissementRepository $etablissementRepository;
    private EntityManagerInterface $entityManager;
    private Security $security;

    /**
     * @param EtablissementRepository $etablissementRepository
     * @param EntityManagerInterface $entityManager
     * @param Security $security
     */
    public function __construct(EtablissementRepository $etablissementRepository, EntityManagerInterface $entityManager, Security $security)
    {
        $this->etablissementRepository = $etablissementRepository;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    #[Route('/favoris', name: 'app_favoris')]
    public function favoris(PaginatorInterface $paginator, Request $request): Response
    {
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_accueil');
        }

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
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_accueil');
        }

        $user = $this->getUser();
        $etablissemnt = $this->etablissementRepository->findOneBy(['slug' => $slug]);
        $user->addFavori($etablissemnt);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }

    #[Route('/favoris/supprime/{slug}', name: 'app_delete_favori_slug')]
    public function deleteFavori($slug): Response
    {
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_accueil');
        }

        $user = $this->getUser();
        $etablissemnt = $this->etablissementRepository->findOneBy(['slug' => $slug]);
        $user->removeFavori($etablissemnt);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $this->redirect($_SERVER['HTTP_REFERER']);
    }
}

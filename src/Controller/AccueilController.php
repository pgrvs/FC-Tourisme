<?php

namespace App\Controller;

use App\Repository\EtablissementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class AccueilController extends AbstractController
{
    private EtablissementRepository $etablissementRepository;

    /**
     * @param EtablissementRepository $etablissementRepository
     */
    public function __construct(EtablissementRepository $etablissementRepository)
    {
        $this->etablissementRepository = $etablissementRepository;
    }

    #[Route('/', name: 'app_accueil')]
    public function getEtablissementsAccueil(PaginatorInterface $paginator, Request $request): Response
    {
        $etablissements = $paginator->paginate(
            $this->etablissementRepository->findBy(["actif" => true, "accueil" => true], ['nom' => 'ASC']),
            $request->query->getInt('page', 1), /*page number*/
            12 /*limit per page*/
        );
        return $this->render('accueil/index.html.twig', [
            'etablissements' => $etablissements,
        ]);
    }
}

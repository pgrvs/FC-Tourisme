<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SetUserPasswordType;
use App\Form\SetUserType;
use App\Form\UserType;
use App\Repository\CategorieRepository;
use App\Repository\EtablissementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;


class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private Security $security;

    /**
     * @param UserRepository $userRepository
     * @param UserPasswordHasherInterface $passwordHasher
     * @param Security $security
     */
    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->security = $security;
    }

    #[Route('/utilisateur/nouveau', name: 'app_creat_account', methods: ['GET', 'POST'], priority: 1)]
    public function newAccount(Request $request): Response
    {
        $user = new User();

        // Création du formulair
        $formUser = $this->createForm(UserType::class, $user);

        // Reconnaitre si le formulaire a été soumis ou pas
        $formUser->handleRequest($request);

        // Est-ce que le formulaire a été soumis
        if ($formUser->isSubmitted() && $formUser->isValid()){
            $passwordHash = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user   ->setActif(true)
                    ->setCreatedAt(new \DateTime())
                    ->setPassword($passwordHash)
                    ->setRoles(["ROLE_USER"]);
            // Insérer l'utilisateur dans la base de données
            $this->userRepository->add($user, true);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/creatAccount.html.twig', [
            'formUser'=> $formUser->createView()
        ]);
    }

    #[Route('/utilisateur', name: 'app_get_account')]
    public function getAccount(): Response
    {
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_accueil');
        }

        $user = $this->getUser();

        return $this->render('user/utilisateur.html.twig', [
            'utilisateur'=> $user
        ]);
    }

    #[Route('/utilisateur/modifier', name: 'app_set_account', methods: ['GET', 'POST'], priority: 1)]
    public function Account(Request $request): Response
    {
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_accueil');
        }

        $user = $this->getUser();

        // Création du formulair
        $formUser = $this->createForm(SetUserType::class, $user);

        // Reconnaitre si le formulaire a été soumis ou pas
        $formUser->handleRequest($request);

        // Est-ce que le formulaire a été soumis
        if ($formUser->isSubmitted() && $formUser->isValid()){
            $user
                ->setUpdateAt(new \DateTime());
            // Insérer l'utilisateur dans la base de données
            $this->userRepository->add($user, true);

            return $this->redirectToRoute('app_get_account');
        }

        return $this->render('user/setAccount.html.twig', [
            'formUser'=> $formUser->createView()
        ]);
    }

    #[Route('/utilisateur/modifier/mot-de-passe', name: 'app_set_password')]
    public function setPassword(Request $request, UserRepository $userRepository): Response
    {
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_accueil');
        }

        $user = $this->getUser();

        // Création du formulair
        $formUser = $this->createForm(SetUserPasswordType::class, $user);

        // Reconnaitre si le formulaire a été soumis ou pas
        $formUser->handleRequest($request);

        // Est-ce que le formulaire a été soumis
        if ($formUser->isSubmitted() && $formUser->isValid()){
            $currentPassword = $formUser->get('password')->getData();
            $newPassword = $formUser->get('newPassword')->getData();
            if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                return $this->render('user/setPassword.html.twig', [
                    'formUser'=> $formUser->createView(),
                    'erreur' => 'Mot de passe actuel incorrect.'
                ]);
            }

            $newPasswordHash = $this->passwordHasher->hashPassword($user, $newPassword);

            $user   ->setUpdateAt(new \DateTime())
                    ->setPassword($newPasswordHash);

            // Insérer l'utilisateur dans la base de données
            $this->userRepository->add($user, true);

            return $this->redirectToRoute('app_get_account');
        }

        return $this->render('user/setPassword.html.twig', [
            'formUser'=> $formUser->createView()
        ]);
    }

    #[Route('/utilisateur/supprimer', name: 'app_delete_account')]
    public function deleteAccount(Request $request, EntityManagerInterface $entityManager,EtablissementRepository $etablissementRepository, UserRepository $userRepository, CategorieRepository $categorieRepository): Response
    {
        if (!$this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('app_accueil');
        }

        $user = $this->getUser();

        foreach ($user->getPosseder() as $etablissement){
            $this->getUser()->removePosseder($etablissement);
            $entityManager->flush();

            foreach ($userRepository->findAll() as $user){
                $user->removeFavori($etablissement);
                $entityManager->flush();
            }

            foreach ($categorieRepository->findAll() as $categorie){
                $categorie->removeEtablissement($etablissement);
                $entityManager->flush();
            }

            $etablissementRepository->remove($etablissement, true);
        }

        foreach ($user->getFavoris() as $favori ){
            $user->removeFavori($favori);
        }
        $entityManager->flush();
        $userRepository->remove($user, true);


        return $this->redirectToRoute('app_accueil');
    }
}
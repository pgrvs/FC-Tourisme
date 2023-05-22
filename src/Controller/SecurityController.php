<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * @param UserRepository $userRepository
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher)
    {
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/index.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): Response
    {
        return $this->redirectToRoute('app_accueil');
    }

    #[Route('/creat-account', name: 'app_creat_account', methods: ['GET', 'POST'], priority: 1)]
    public function newAccount(Request $request): Response
    {
        $user = new User();

        // Création du formulair
        $formUser = $this->createForm(UserType::class, $user);


        // Reconnaitre si le formulaire a été soumis ou pas
        $formUser->handleRequest($request);
        // Est-ce que le formulaire a été soumis
        if ($formUser->isSubmitted() && $formUser->isValid()){
            $passwordHash = $this->passwordHasher->hashPassword($user , $user->getPassword());
            $user   ->setActif(true)
                    ->setCreatedAt(new \DateTime())
                    ->setPassword($passwordHash)
                    ->setRoles([$formUser['roles']->getData()]);
            // Insérer l'utilisateur dans la base de données
            $this->userRepository->add($user, true);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/creatAccount.html.twig', [
            'formUser'=> $formUser->createView()
        ]);
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    /**
     * @param UserPasswordHasherInterface $passwordHasher
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");
        $userRoles = ['ROLE_USER','ROLE_PROPRIETAIRE', 'ROLE_ADMIN'];
        for ($i=0; $i<20; $i++) {
            $date = $faker->dateTimeBetween('-6months');
            $utilisateur = new User();
            $utilisateur->setPrenom($faker->firstName);
            $utilisateur->setNom($faker->lastName);
            $utilisateur->setEmail($faker->email);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $utilisateur,
                'motDePasse'
            );
            $utilisateur->setPassword($hashedPassword);
            if ($faker->numberBetween(0,2) === 0) {
                $utilisateur->setPseudo($faker->userName());
            }
            $utilisateur->setCreatedAt($date);
            if ($faker->numberBetween(0,5) === 0){
                $utilisateur->setUpdateAt(new DateTime());
            }
            $utilisateur->setActif($faker->boolean());
            $utilisateur->setRoles((array)$userRoles[$faker->numberBetween(0, 2)]);
            $manager->persist($utilisateur);
        }

        $manager->flush();
    }
}

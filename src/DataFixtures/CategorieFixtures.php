<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategorieFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");
        $libelleCategorie = [
            'restaurant',
            'hôtel',
            'gîte',
            'musée',
            'artisanat'
        ];
        for ($i=0; $i<=4;$i++){
            $categorie = new Categorie();
            $categorie  ->setNom($libelleCategorie[$i])
                        ->setCreatedAt($faker->dateTimeBetween('-6months'));

            $this->addReference("categorie".$i,$categorie);

            $manager->persist($categorie);
        }

        $manager->flush();
    }

}

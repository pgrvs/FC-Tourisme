<?php

namespace App\DataFixtures;

use App\Entity\Etablissement;
use App\Repository\CategorieRepository;
use App\Repository\VilleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class EtablissementFixtures extends Fixture
{
    private SluggerInterface $slugger;
    private VilleRepository $villeRepository;
    private CategorieRepository $categorieRepository;

    /**
     * @param SluggerInterface $slugger
     * @param VilleRepository $villeRepository
     * @param CategorieRepository $categorieRepository
     */
    public function __construct(SluggerInterface $slugger, VilleRepository $villeRepository, CategorieRepository $categorieRepository)
    {
        $this->slugger = $slugger;
        $this->villeRepository = $villeRepository;
        $this->categorieRepository = $categorieRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");
        $villes = $this->villeRepository->findAll();

        for ($i=0;$i<50;$i++){
            $etablissement = new Etablissement();
            $etablissement  ->setNom($faker->company)
                            ->setSlug(($this->slugger->slug($etablissement->getNom())->lower()))
                            ->setVille($villes[$faker->numberBetween(0, count($villes)-1)])
                            ->setDescription($faker->paragraph())
                            ->setNumTelephone($faker->phoneNumber())
                            ->setAdresse($faker->streetAddress())
                            ->setEmail(str_replace(' ', '',strtolower($etablissement->getNom())).$faker->freeEmailDomain())
                            ->setActif($faker->boolean())
                            ->setAccueil($faker->boolean())
                            ->setCreatedAt($faker->dateTime())
                            ->setUpdatedAt($faker->dateTimeBetween('-6months'));

            $numCat = $faker->randomElements([0, 1, 2, 3, 4], $faker->numberBetween(1,3));
            foreach ($numCat as $num)
            {
                $cat = $this->getReference("categorie".$num);
                $etablissement->addCategorie($cat);
            }
            $manager->persist($etablissement);

        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CategorieFixtures::class
        ];
    }
}

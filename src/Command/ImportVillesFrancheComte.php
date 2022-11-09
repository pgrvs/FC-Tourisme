<?php

namespace App\Command;

use App\Entity\Ville;
use App\Repository\VilleRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use League\Csv\Reader;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:import-villes-franche-comte')]
class ImportVillesFrancheComte extends Command
{
    private VilleRepository $villeRepository;

    /**
     * @param VilleRepository $villeRepository
     */
    public function __construct(VilleRepository $villeRepository)
    {
        $this->villeRepository = $villeRepository;
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $reader = Reader::createFromPath('src\Command\villes.csv', 'r');
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);
        $records = $reader->getRecords();
        foreach ($records as $offset => $record) {
            if ($record['Département'] == 25 || $record['Département'] == 70 || $record['Département'] == 90 || $record['Département'] == 39) {
                $ville = new Ville();
                $ville->setCodePostal($record['Code postal']);
                $ville->setNom($record['Commune']);
                $ville->setNomDepartement($record['Nom département']);
                $ville->setNumDepartement($record['Département']);
                $ville->setNomRegion($record['Région']);

                $this->villeRepository->save($ville, true);
            }
        }
        return Command::SUCCESS;
    }
}
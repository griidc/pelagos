<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\Fileset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'pelagos:get-filetypes',
    description: 'Lists filetypes and count per UDI as detected in current fileset.',
)]
class PelagosGetFiletypesCommand extends Command
{
    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     */
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $datasets = $this->entityManager->getRepository(Dataset::class)->findAll();
        foreach ($datasets as $dataset) {
            $udi = $dataset->getUdi();
            $fileTypes = $this->getFileTypes($dataset);
            print "$udi,$fileTypes\n";
        }

        $io->success('List Complete');

        return Command::SUCCESS;
    }

    /**
     * Get list of filetypes used per UDI.
     */
    private function getFileTypes(Dataset $dataset): ?string
    {
        $datasetSubmission = $dataset->getDatasetSubmission();
        if ($datasetSubmission instanceof DatasetSubmission) {
            $fileSet = $datasetSubmission->getFileset();
            if ($fileSet instanceof Fileset) {
                $fileTypes = [];
                foreach ($fileSet->getAllFiles() as $file) {
                    $type = pathinfo($file->getFilePathName(), PATHINFO_EXTENSION);
                    if (array_key_exists($type, $fileTypes)) {
                        $fileTypes[$type]++;
                    } else {
                        $fileTypes[$type] = 1;
                    }
                }
                $fileList = '';
                foreach ($fileTypes as $key => $value) {
                    $fileList .= $key . ',' . $value . ',';
                }
                $fileList = rtrim($fileList, ',');
                return $fileList;
            }
        }
        return null;
    }
}

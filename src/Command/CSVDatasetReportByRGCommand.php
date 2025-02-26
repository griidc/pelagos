<?php

namespace App\Command;

use App\Entity\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:dataset-report-csv-by-rg', description: 'CSV of udi, doi, title, abstract, authors, pubdate for RG')]
class CSVDatasetReportByRGCommand extends Command
{
    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('outputFileName', InputArgument::REQUIRED, 'What is the output filename?')
            ->addArgument('RGID', InputArgument::REQUIRED, 'What is Research Group ID?');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        new SymfonyStyle($input, $output);

        $outputFileName = $input->getArgument('outputFileName');
        $researchGroupId = $input->getArgument('RGID');

        $outputFile = fopen($outputFileName, 'w');
        $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(
            ['researchGroup' => $researchGroupId]
        );

        fputcsv($outputFile, ['UDI', 'DOI', 'TITLE', 'AUTHOR(S)', 'ACCEPTED DATE', 'ABSTRACT']);
        foreach ($datasets as $dataset) {
            /** @var Dataset $dataset */
            $udi = $dataset->getUdi();
            $doi = $dataset->getDoi();
            $title = $dataset->getTitle();
            $abstract = $dataset->getAbstract();
            $authors = $dataset->getAuthors();
            $publishedDate = $dataset->getAcceptedDate() ? $dataset->getAcceptedDate()->format('Y-m-d') : null;
            fputcsv($outputFile, [$udi, $doi, $title, $authors, $publishedDate, $abstract]);
        }
        fclose($outputFile);
        return Command::SUCCESS;
    }
}

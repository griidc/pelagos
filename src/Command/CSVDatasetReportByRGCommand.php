<?php

namespace App\Command;

use App\Entity\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CSVDatasetReportByRGCommand extends Command
{
    protected static $defaultName = 'pelagos:dataset-report-csv-by-rg';
    protected static $defaultDescription = 'CSV of udi, doi, title, abstract, authors, pubdate for RG';

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
            ->setDescription(self::$defaultDescription)
            ->addArgument('outputFileName', InputArgument::REQUIRED, 'What is the output filename?')
            ->addArgument('RGID', InputArgument::REQUIRED, 'What is Research Group ID?');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $outputFileName = $input->getArgument('outputFileName');
        $researchGroupId = $input->getArgument('RGID');

        $outputFile = fopen($outputFileName, 'w');
        $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(
            array('researchGroup' => $researchGroupId)
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
        return 0;
    }
}

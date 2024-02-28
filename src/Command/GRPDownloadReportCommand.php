<?php
namespace App\Command;

use App\Entity\Dataset;
use App\Repository\DatasetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'pelagos:grp-download-report',
    description: 'CSV of timestamp, udi, title, FC, RG, Size (mb)',
)]
class GRPDownloadReportCommand extends Command
{
    public const GRP_FUNDING_ORG_ID = 15;
    public const GRP_SHORTNAME = 'NAS';

    /**
     * Class constructor for dependency injection.
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DatasetRepository $datasetRepository,
    ) {
        $this->entityManager = $entityManager;
        $this->datasetRepository = $datasetRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDefinition(
            new InputDefinition([
                new InputOption('uploads', 'u', InputOption::VALUE_NONE),
            ])
        );
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $upload = $input->getOption('uploads');
        if ($upload) {
            $this->generateUploadsReport();
        } else {
            $this->generateDownloadReport();
        }
        return Command::SUCCESS;
    }

    /**
     * Generates the GRP upload report
     */
    protected function generateUploadsReport(): void
    {
        // count, funding cycle, research group
        $outfile = fopen('php://output', 'w');
        $headings = [
            'udi',
            'has submission',
            'funding cycle',
            'research group',
            'size (MB)'
        ];
        fputcsv($outfile, $headings);

        $datasets = $this->datasetRepository->findAll();
        foreach ($datasets as $dataset) {
            if ($dataset->getResearchGroup()->getFundingCycle()->getFundingOrganization()->getShortName() === self::GRP_SHORTNAME) {

                $sizeMB = $dataset->hasDatasetSubmission() && $dataset->getDatasetSubmission()->getFileset() ? round((($dataset->getDatasetSubmission()->getFileset()->getFileSize()) / 1000000), 1) : 0;
                $fields = [
                    $dataset->getUdi(),
                    $dataset->hasDatasetSubmission() ? "Yes" : "No",
                    $dataset->getResearchGroup()->getFundingCycle()->getName(),
                    $dataset->getResearchGroup()->getName(),
                    $sizeMB
                ];

                fputcsv($outfile, $fields);
            }
        }
    }

    /**
     * Generates the GRP download report.
     */
    protected function generateDownloadReport(): void
    {
        $outfile = fopen('php://output', 'w');
        $headings = [
            'timestamp',
            'udi',
            'title',
            'funding cycle',
            'research group',
            'size (MB)'
        ];
        fputcsv($outfile, $headings);

        $qb = $this->entityManager->createQueryBuilder();
        $query = $qb
            ->select('log.creationTimeStamp, d.id')
            ->from('\App\Entity\LogActionItem', 'log')
            ->join('\App\Entity\Dataset', 'd', Join::WITH, 'log.subjectEntityId = d.id')
            ->where('log.subjectEntityName = ?1')
            ->andWhere('log.actionName = ?2')
            ->orderBy('log.creationTimeStamp', 'DESC')
            ->setParameter(1, 'Pelagos\Entity\Dataset')
            ->setParameter(2, 'File Download')
            ->getQuery();

        $results = $query->getResult();

        foreach ($results as $result) {
            $id = $result['id'];
            $timestamp = $result['creationTimeStamp']->format('Y-m-d H:i:s');
            if ($this->isGRP($id)) {
                $dataset = $this->getDataset($id);
                if ($dataset instanceof Dataset) {
                    $title = $dataset->getTitle(); // Is never null because of isGRP success.
                    $rg = $dataset->getResearchGroup(); // Likewise can not be null.
                    $fc = $rg->getFundingCycle(); // Same here.
                    $sizeMB = round((($dataset->getDatasetSubmission()->getFileset()->getFileSize()) / 1000000), 1);

                    $fields = [
                                $timestamp,
                                $dataset->getUdi(),
                                $title,
                                $fc->getName(),
                                $rg->getName(),
                                $sizeMB
                            ];

                    fputcsv($outfile, $fields);
                }
            }
        }
        fclose($outfile);
    }

    /**
     * Returns true if id passed is that of a GRP dataset, false otherwise.
     *
     * @param int $id The ID of a Pelagos dataset.
     */
    protected function isGRP($id): bool
    {
        $dataset = $this->entityManager->find('App\Entity\Dataset', $id);
        if ($dataset instanceof Dataset) {
            $foid = $dataset->getResearchGroup()->getFundingCycle()->getFundingOrganization()->getId();
        } else {
            $foid = null;
        }
        return ($foid == self::GRP_FUNDING_ORG_ID);
    }

    /**
     * Return dataset given an ID, or null if not found.
     *
     * @param int $id The ID of a Pelagos dataset.
     */
    protected function getDataset($id): Dataset|null
    {
        return $this->entityManager->find('\App\Entity\Dataset', $id);
    }
}

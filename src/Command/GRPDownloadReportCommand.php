<?php

namespace App\Command;

use App\Entity\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Query\Expr\Join;

class GRPDownloadReportCommand extends Command
{
    public const GRP_FUNDING_ORG_ID = 15;
    protected static $defaultName = 'pelagos:grp-download-report';
    protected static $defaultDescription = 'CSV of timestamp, udi, title, FC, RG, Size (mb)';

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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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
            if($this->isGRP($id)) {
                $dataset = $this->getDataset($id);
                $title = $dataset?->getTitle(); // Is never null because of isGRP success.
                $rg = $dataset?->getResearchGroup(); // Likewise can not be null.
                $fc = $rg?->getFundingCycle(); // Same here.
                $sizeMB = round((($dataset->getDatasetSubmission()->getFileset()->getFileSize()) / 1000000), 1);

                $fields = [
                            $timestamp,
                            $dataset->getUdi(),
                            $title,
                            $fc?->getName(),
                            $rg?->getName(),
                            $sizeMB
                        ];

                fputcsv($outfile, $fields);
            }
        }
        fclose($outfile);
        return 0;
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

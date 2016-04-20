<?php

namespace Pelagos\Factory;

use Doctrine\ORM\EntityManager;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DIF;

/**
 * A factory class that creates new Datasets.
 */
class DatasetFactory
{
    /**
     * The entity manager to use.
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager The entity manager to use.
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Create a Dataset.
     *
     * @param DIF $dif The DIF that identifies this Dataset.
     *
     * @return Dataset A new Dataset.
     */
    public function createDataset(DIF $dif)
    {
        $dataset = new Dataset($dif);
        // Get the UDI prefix for the funding cycle.
        $udiPrefix = $dif->getResearchGroup()->getFundingCycle()->getUdiPrefix();
        // If it's set.
        if (null !== $udiPrefix) {
            // Get the Research Group id.
            $researchGroupId = $dif->getResearchGroup()->getId();
            // Build all but the sequence of the UDI.
            $udi = sprintf('%s.x%03d.000:', $udiPrefix, $researchGroupId);

            // Get the dataset with the highest sequence for the same Funding Cycle and Research Group.
            $query = $this->entityManager->getRepository(Dataset::class)->createQueryBuilder('d')
                ->where('d.udi LIKE :udi')
                ->orderBy('d.udi', 'DESC')
                ->setParameter('udi', "$udi%")
                ->getQuery();

            $datasets = $query->getResult();

            if (count($datasets) == 0) {
                // If this is the first dataset for this Research Group, we start at 1.
                $sequence = 1;
            } else {
                // Grab the sequence from the UID.
                preg_match('/:(\d{4})$/', $datasets[0]->getUdi(), $matches);
                $lastSequence = $matches[1];
                // Add one.
                $sequence = (intval($lastSequence) + 1);
            }
            // Append the sequence to the UDI.
            $udi .= sprintf('%04d', $sequence);
            $dataset->setUdi($udi);
        }
        return $dataset;
    }
}

<?php

namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;

use Pelagos\Entity\Dataset;

/**
 * A utility class for UDIs.
 */
class Udi
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
     * Mint an UDI for a Dataset.
     *
     * @param Dataset $dataset The Dataset to mint an UDI for.
     *
     * @return string The UDI that was minted.
     */
    public function mintUdi(Dataset $dataset)
    {
        // Get the UDI prefix for the funding cycle.
        $udiPrefix = $dataset->getResearchGroup()->getFundingCycle()->getUdiPrefix();
        // If there isn't one.
        if (null === $udiPrefix) {
            // We can't make an UDI.
            return null;
        }
        // Get the Research Group id.
        $researchGroupId = $dataset->getResearchGroup()->getId();
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
        return $udi;
    }
}

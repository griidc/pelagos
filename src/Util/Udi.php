<?php

namespace App\Util;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Dataset;
use App\Entity\Udi as UdiEntity;

/**
 * A utility class for UDIs.
 */
class Udi
{
    /**
     * The entity manager to use.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager The entity manager to use.
     */
    public function __construct(EntityManagerInterface $entityManager)
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
        $datasets = $this->entityManager->getRepository(Dataset::class)->createQueryBuilder('d')
            ->where('d.udi LIKE :udi')
            ->orderBy('d.udi', 'DESC')
            ->setParameter('udi', "$udi%")
            ->getQuery()
            ->getResult();

        // Get a list of UDI's issued.
        $udis = $this->entityManager->getRepository(UdiEntity::class)->createQueryBuilder('u')
                ->where('u.uniqueDataIdentifier LIKE :udi')
                ->orderBy('u.uniqueDataIdentifier', 'DESC')
                ->setParameter('udi', "$udi%")
                ->getQuery()
                ->getResult();

        if (count($datasets) !== 0) {
            // Find the latest dataset submitted.
            preg_match('/:(\d{4})$/', $datasets[0]->getUdi(), $matches);
            $lastDatasetSequence = intval($matches[1]);
        } else {
            $lastDatasetSequence = 0;
        }

        if (count($udis) !== 0) {
            // Grab the last sequence from the UDI list.
            preg_match('/:(\d{4})$/', $udis[0], $matches);
            $lastUdiSequence = intval($matches[1]);
        } else {
            $lastUdiSequence = 0;
        }

        $lastSequence = max($lastDatasetSequence, $lastUdiSequence);
        // Add one. (If lastSequence = 0, this will be the first one).
        $sequence = ($lastSequence + 1);

        // Append the sequence to the UDI.
        $udi .= sprintf('%04d', $sequence);

        $udiEntity = new UdiEntity($udi);
        $this->entityManager->persist($udiEntity);
        $this->entityManager->flush();

        $dataset->setUdi($udi);
        return $udi;
    }
}

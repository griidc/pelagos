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
        $query = $this->entityManager->getRepository(UdiEntity::class)->createQueryBuilder('u')
            ->where('u.uniqueDataIdentifier LIKE :udi')
            ->orderBy('u.uniqueDataIdentifier', 'DESC')
            ->setParameter('udi', "$udi%")
            ->getQuery();

        $udis = $query->getResult();

        if (count($udis) === 0) {
            // If this is the first dataset for this Research Group, we start at 1.
            $sequence = 1;
        } else {
            // Grab the sequence from the UID.
            preg_match('/:(\d{4})$/', $udis[0], $matches);
            $lastSequence = $matches[1];
            // Add one.
            $sequence = (intval($lastSequence) + 1);
        }
        // Append the sequence to the UDI.
        $udi .= sprintf('%04d', $sequence);

        $udiEntity = new UdiEntity($udi);
        $this->entityManager->persist($udiEntity);
        $this->entityManager->flush($udiEntity);

        $dataset->setUdi($udi);
        return $udi;
    }
}

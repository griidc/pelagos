<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use App\Entity\ResearchGroup;
use App\Util\FundingOrgFilter;

/**
 * Research Group Entity Repository class.
 */
class ResearchGroupRepository extends ServiceEntityRepository
{
    /**
     * Utility to filter by funding organization.
     *
     * @var FundingOrgFilter
     */
    private $fundingOrgFilter;

    /**
     * Constructor.
     *
     * @param ManagerRegistry  $registry         The Registry Manager.
     * @param FundingOrgFilter $fundingOrgFilter Utility to filter by funding organization.
     */
    public function __construct(ManagerRegistry $registry, FundingOrgFilter $fundingOrgFilter)
    {
        parent::__construct($registry, ResearchGroup::class);

        $this->fundingOrgFilter = $fundingOrgFilter;
    }

    /**
     * Count the number of Reseach Groups.
     *
     * @return integer
     */
    public function countResearchGroups()
    {
        $queryBuilder = $this->createQueryBuilder('researchGroup');

        $queryBuilder
        ->select($queryBuilder->expr()->count('researchGroup.id'));

        if ($this->fundingOrgFilter->isActive()) {
            $queryBuilder->where('researchGroup.id IN (:rgs)');
            $queryBuilder->setParameter('rgs', $this->fundingOrgFilter->getResearchGroupsIdArray());
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * Get research group information for the aggregations.
     *
     * @param array $aggregations Aggregations for each research id.
     *
     * @return array
     */
    public function getResearchGroupsInfo(array $aggregations): array
    {
        $researchGroupsInfo = array();

        $researchGroups = $this->findBy(array('id' => array_keys($aggregations)));

        foreach ($researchGroups as $researchGroup) {
            $researchGroupsInfo[$researchGroup->getId()] = array(
                'id' => $researchGroup->getId(),
                'name' => $researchGroup->getName(),
                'shortName' => $researchGroup->getShortName(),
                'count' => $aggregations[$researchGroup->getId()]
            );
        }

        //Sorting based on highest count
        $array_column = array_column($researchGroupsInfo, 'count');
        array_multisort($array_column, SORT_DESC, $researchGroupsInfo);

        return $researchGroupsInfo;
    }

    /**
     * Find the lowest-value ID available for use, in a specified range.
     *
     * @param  int $min Range start.
     * @param  int $max Range end.
     *
     * @return int First available ID.
     */
    public function getNextAvailableId($min, $max): int
    {
        $qb = $this->createQueryBuilder('researchGroup')
            ->select('researchGroup.id')
            ->orderBy('researchGroup.id', 'ASC');
        $existingIds = $qb->getQuery()->getResult(QUERY::HYDRATE_SCALAR_COLUMN);
        $availableIds = array_values(array_diff(range($min, $max), $existingIds));
        return reset($availableIds);
    }
}

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
}

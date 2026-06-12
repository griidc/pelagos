<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use App\Entity\ResearchGroup;
use App\Entity\FundingOrganization;
use App\Util\FundingOrgFilter;

/**
 * Research Group Entity Repository class.
 *
 * @extends ServiceEntityRepository<ResearchGroup>
 *
 * @method ResearchGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResearchGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResearchGroup[]    findAll()
 * @method ResearchGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
     * Find the lowest-value ID available for a new Research Group.
     */
    public function getNextAvailableId(): int
    {
        $qb = $this->createQueryBuilder('researchGroup')
            ->select('researchGroup.id')
            ->orderBy('researchGroup.id', 'ASC');
        $existingIds = $qb->getQuery()->getResult(QUERY::HYDRATE_SCALAR_COLUMN);
        $availableIds = array_values(array_diff(range(ResearchGroup::MIN_ID, ResearchGroup::MAX_ID), $existingIds));
        return reset($availableIds);
    }

    public function getResearchGroupList(): array
    {
        $researchGroups = $this->createQueryBuilder('researchGroup')
            ->select('researchGroup.name, researchGroup.id')
            ->orderBy('researchGroup.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        // Return as an associative array with id as key
        $researchGroups = array_column($researchGroups, 'id', 'name');
        // If array_column fails, return an empty array
        if ($researchGroups === false) {
            $researchGroups = [];
        }

        return $researchGroups;
    }

    /**
     * Get all research groups associated with a specific funding organization.
     *
     * @param FundingOrganization $fundingOrganization The funding organization to filter by.
     * @param array $orderBy Optional sort order (default: name ASC).
     *
     * @return ResearchGroup[] Array of research groups for the given funding organization.
     */
    public function findByFundingOrganization(FundingOrganization $fundingOrganization, array $orderBy = ['name' => 'ASC']): array
    {
        $qb = $this->createQueryBuilder('researchGroup')
            ->join('researchGroup.fundingCycle', 'fundingCycle')
            ->where('fundingCycle.fundingOrganization = :fundingOrganization')
            ->setParameter('fundingOrganization', $fundingOrganization);

        foreach ($orderBy as $field => $order) {
            $qb->addOrderBy("researchGroup.$field", $order);
        }

        return $qb->getQuery()->getResult();
    }
}

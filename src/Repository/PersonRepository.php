<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use App\Entity\Person;
use App\Util\FundingOrgFilter;

/**
 * Person Entity Repository class.
 */
class PersonRepository extends ServiceEntityRepository
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
        parent::__construct($registry, Person::class);

        $this->fundingOrgFilter = $fundingOrgFilter;
    }

    /**
     * Count the number of people.
     *
     * @return integer
     */
    public function countPeople()
    {
        $queryBuilder = $this->createQueryBuilder('person');

        $queryBuilder
        ->select($queryBuilder->expr()->count('person.id'))
        ->where(
            $queryBuilder->expr()->gt(
                'person.id',
                $queryBuilder->expr()->literal(0)
            )
        );

        if ($this->fundingOrgFilter->isActive()) {
            $queryBuilder->innerJoin('person.personResearchGroups', 'prg');
            $queryBuilder->innerJoin('prg.researchGroup', 'rg');
            $queryBuilder->where('rg.id IN (:rgs)');
            $queryBuilder->setParameter('rgs', $this->fundingOrgFilter->getResearchGroupsIdArray());
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * Get unique organizations from the Person entity.
     */
    public function getUniqueOrganizations(): array
    {
        $queryBuilder = $this->createQueryBuilder('person');

        $queryBuilder
        ->select('DISTINCT person.organization')
        ->where($queryBuilder->expr()->isNotNull('person.organization'))
        ->andWhere($queryBuilder->expr()->neq('person.organization', $queryBuilder->expr()->literal('')))
        ->orderBy('person.organization', 'ASC');

        return $queryBuilder->getQuery()->getSingleColumnResult();
    }

    /**
     * Get unique positions from the Person entity.
     */
    public function getUniquePositions(): array
    {
        $queryBuilder = $this->createQueryBuilder('person');

        $queryBuilder
        ->select('DISTINCT person.position')
        ->orderBy('person.position', 'ASC');

        return $queryBuilder->getQuery()->getSingleColumnResult();
    }
}

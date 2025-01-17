<?php

namespace App\Repository;

use App\Entity\PersonResearchGroup;
use App\Entity\ResearchGroupRole;
use App\Util\FundingOrgFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Person Research group Entity Repository class.
 *
 * @extends ServiceEntityRepository<PersonResearchGroup>
 *
 * @method PersonResearchGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonResearchGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonResearchGroup[]    findAll()
 * @method PersonResearchGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonResearchGroupRepository extends ServiceEntityRepository
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
        parent::__construct($registry, PersonResearchGroup::class);

        $this->fundingOrgFilter = $fundingOrgFilter;
    }

    /**
     * Returns the list of people with leadership role.
     *
     * @return array
     */
    public function getLeadershipPeople(): array
    {
        $queryBuilder = $this->createQueryBuilder('person_research_group');
        $queryBuilder
            ->select('person_research_group')
            ->innerJoin('person_research_group.person', 'person')
            ->innerJoin('person_research_group.role', 'role')
            ->innerJoin('person_research_group.researchGroup', 'rg')
            ->where('role.name = :leadership')
            ->setParameter('leadership', ResearchGroupRole::LEADERSHIP)
            ->orderBy('person.lastName', 'ASC');

        if ($this->fundingOrgFilter->isActive()) {
            $queryBuilder->andWhere('rg.id IN (:rgs)');
            $queryBuilder->setParameter('rgs', $this->fundingOrgFilter->getResearchGroupsIdArray());
        }

        return $queryBuilder->getQuery()->getResult();
    }
}

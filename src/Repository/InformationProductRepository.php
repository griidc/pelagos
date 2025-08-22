<?php

namespace App\Repository;

use App\Entity\DigitalResourceTypeDescriptor;
use App\Entity\FundingCycle;
use App\Entity\InformationProduct;
use App\Entity\Person;
use App\Entity\ProductTypeDescriptor;
use App\Util\FundingOrgFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InformationProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method InformationProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method InformationProduct[]    findAll()
 * @method InformationProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InformationProductRepository extends ServiceEntityRepository
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
        parent::__construct($registry, InformationProduct::class);
        $this->fundingOrgFilter = $fundingOrgFilter;
    }

    /**
     * Find one by research group id.
     *
     * @param integer $researchGroupId Research group id associated.
     *
     * @return array
     */
    public function findOneByResearchGroupId(int $researchGroupId): array
    {
        return $this->createQueryBuilder('informationProduct')
            ->innerJoin('informationProduct.researchGroups', 'rg')
            ->andWhere('rg.id = :val')
            ->setParameter('val', $researchGroupId)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find by person.
     *
     * @return array
     */
    public function findByPerson(Person $person): array
    {
        $qb = $this->createQueryBuilder('informationProduct');
        return $qb
            ->innerJoin('informationProduct.researchGroups', 'researchGroup')
            ->innerJoin('researchGroup.personResearchGroups', 'personResearchGroup')
            ->innerJoin('personResearchGroup.person', 'person')
            ->where('person.id = :personId')
            ->setParameter('personId', $person->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find by funding cycle.

     * @return array
     */
    public function findByFundingCycle(FundingCycle $fundingCycle): array
    {
        $qb = $this->createQueryBuilder('informationProduct');
        return $qb
            ->innerJoin('informationProduct.researchGroups', 'researchGroup')
            ->join('researchGroup.fundingCycle', 'fundingCycle')
            ->where('fundingCycle.id = :fundingCycleId')
            ->setParameter('fundingCycleId', $fundingCycle->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get Information Products by Digital Resource Type.
     *
     * @param DigitalResourceTypeDescriptor $digitalResourceTypeDescriptor
     *
     * @return array An array of Information Products.
     */
    public function findByDigitalResourceTypeDescriptor(DigitalResourceTypeDescriptor $digitalResourceTypeDescriptor): array
    {
        $qb = $this->createQueryBuilder('ip');
        $qb->setParameter('digitalResourceTypeDescriptor', $digitalResourceTypeDescriptor);
        $qb->where($qb->expr()->isMemberOf(':digitalResourceTypeDescriptor', 'ip.digitalResourceTypeDescriptors'));

        return $qb->getQuery()->getResult();
    }

    /**
     * Get Information Products by Product Type Descriptor.
     *
     * @param ProductTypeDescriptor $productTypeDescriptor
     *
     * @return array An array of Information Products.
     */
    public function findByProductTypeDescriptor(ProductTypeDescriptor $productTypeDescriptor): array
    {
        $qb = $this->createQueryBuilder('ip');
        $qb->setParameter('productTypeDescriptor', $productTypeDescriptor);
        $qb->where($qb->expr()->isMemberOf(':productTypeDescriptor', 'ip.productTypeDescriptors'));

        return $qb->getQuery()->getResult();
    }

    /**
     * Creates a new QueryBuilder instance that is prepopulated for this entity and provides a sorted result.
     *
     * @param string $alias   The Entity alias.
     * @param string $indexBy The index for the from.
     *
     * @return QueryBuilder
     */
    public function createSortedQueryBuilder(string $alias, string $indexBy = null)
    {
        $qb = $this->createQueryBuilder($alias)
            ->select($alias);
            // ->from($entityName, $alias, $indexBy);

        if ($this->fundingOrgFilter->isActive()) {
            $researchGroupIds = $this->fundingOrgFilter->getResearchGroupsIdArray();

            $qb
                ->innerJoin($alias . '.researchGroups', 'rg')
                ->andWhere('rg.id IN (:rgs)')
                ->setParameter('rgs', $researchGroupIds);
        }

        $qb->orderBy($alias . '.id');

        return $qb;
    }

    /*
    public function findOneBySomeField($value): ?InformationProduct
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

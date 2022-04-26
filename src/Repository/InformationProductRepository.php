<?php

namespace App\Repository;

use App\Entity\DigitalResourceTypeDescriptor;
use App\Entity\InformationProduct;
use App\Entity\ProductTypeDescriptor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InformationProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method InformationProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method InformationProduct[]    findAll()
 * @method InformationProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InformationProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InformationProduct::class);
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

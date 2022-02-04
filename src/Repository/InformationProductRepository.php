<?php

namespace App\Repository;

use App\Entity\InformationProduct;
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
     * @param integer $rgId Research group id associated.
     *
     * @return array
     */
    public function findOneByResearchGroupId(int $rgId): array
    {
        return $this->createQueryBuilder('informationProduct')
            ->innerJoin('informationProduct.researchGroups', 'rg')
            ->andWhere('rg.id = :val')
            ->setParameter('val', $rgId)
            ->getQuery()
            ->getResult()
        ;
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

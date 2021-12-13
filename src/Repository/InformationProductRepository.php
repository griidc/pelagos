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

    // /**
    //  * @return InformationProduct[] Returns an array of InformationProduct objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

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

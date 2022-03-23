<?php

namespace App\Repository;

use App\Entity\InformationProductType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InformationProductType|null find($id, $lockMode = null, $lockVersion = null)
 * @method InformationProductType|null findOneBy(array $criteria, array $orderBy = null)
 * @method InformationProductType[]    findAll()
 * @method InformationProductType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InformationProductTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InformationProductType::class);
    }

    // /**
    //  * @return InformationProductType[] Returns an array of InformationProductType objects
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
    public function findOneBySomeField($value): ?InformationProductType
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

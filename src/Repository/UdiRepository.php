<?php

namespace App\Repository;

use App\Entity\Udi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Udi|null find($id, $lockMode = null, $lockVersion = null)
 * @method Udi|null findOneBy(array $criteria, array $orderBy = null)
 * @method Udi[]    findAll()
 * @method Udi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UdiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Udi::class);
    }

    // /**
    //  * @return Udi[] Returns an array of Udi objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Udi
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

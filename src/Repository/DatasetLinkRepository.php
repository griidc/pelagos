<?php

namespace App\Repository;

use App\Entity\DatasetLinks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method DatasetLinks|null find($id, $lockMode = null, $lockVersion = null)
 * @method DatasetLinks|null findOneBy(array $criteria, array $orderBy = null)
 * @method DatasetLinks[]    findAll()
 * @method DatasetLinks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatasetLinkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DatasetLinks::class);
    }

    // /**
    //  * @return DatasetLinks[] Returns an array of DatasetLinks objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DatasetLinks
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

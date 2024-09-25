<?php

namespace App\Repository;

use App\Entity\FundingCycle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FundingCycle>
 *
 * @method FundingCycle|null find($id, $lockMode = null, $lockVersion = null)
 * @method FundingCycle|null findOneBy(array $criteria, array $orderBy = null)
 * @method FundingCycle[]    findAll()
 * @method FundingCycle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FundingCycleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FundingCycle::class);
    }

//    /**
//     * @return FundingCycle[] Returns an array of FundingCycle objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FundingCycle
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

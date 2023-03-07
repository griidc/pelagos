<?php

namespace App\Repository;

use App\Entity\GCMDKeyword;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GCMDKeyword>
 *
 * @method GCMDKeyword|null find($id, $lockMode = null, $lockVersion = null)
 * @method GCMDKeyword|null findOneBy(array $criteria, array $orderBy = null)
 * @method GCMDKeyword[]    findAll()
 * @method GCMDKeyword[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GCMDKeywordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GCMDKeyword::class);
    }

    public function save(GCMDKeyword $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GCMDKeyword $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return GCMDKeyword[] Returns an array of GCMDKeyword objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?GCMDKeyword
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

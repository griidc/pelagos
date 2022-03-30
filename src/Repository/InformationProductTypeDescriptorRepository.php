<?php

namespace App\Repository;

use App\Entity\InformationProductTypeDescriptor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InformationProductTypeDescriptor|null find($id, $lockMode = null, $lockVersion = null)
 * @method InformationProductTypeDescriptor|null findOneBy(array $criteria, array $orderBy = null)
 * @method InformationProductTypeDescriptor[]    findAll()
 * @method InformationProductTypeDescriptor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InformationProductTypeDescriptorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InformationProductTypeDescriptor::class);
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

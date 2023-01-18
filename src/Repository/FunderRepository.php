<?php

namespace App\Repository;

use App\Entity\Funder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Funder>
 *
 * @method Funder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Funder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Funder[]    findAll()
 * @method Funder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FunderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Funder::class);
    }

    public function save(Funder $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Funder $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * This method queries Funders by partial match of name, returning name and ID.
     *
     * @param string|null $userQueryString The user query.
     *
     * @return array Returns an array of Funder objects.
     */
    public function findFunderByPartialName(?string $userQueryString): array
    {
        $qb = $this->createQueryBuilder('f')
            ->select('f.id, f.name')
            ->where('LOWER(f.name) like LOWER(:queryString)')
            ->setParameter('queryString', "%$userQueryString%");

            return $qb->getQuery()->getArrayResult();
    }

//    /**
//     * @return Funder[] Returns an array of Funder objects
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

//    public function findOneBySomeField($value): ?Funder
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

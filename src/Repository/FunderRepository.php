<?php

namespace App\Repository;

use App\Entity\Funder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Funder Repository.
 *
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
     * @param string|null $userQueryString the user query
     *
     * @return array returns an array of Funder objects
     */
    public function findFunderByPartialName(?string $userQueryString): array
    {
        $userQueryString = $userQueryString ?? '';
        $qb = $this->createQueryBuilder('f')
            ->select('f.id, f.name')
            ->where('LOWER(f.name) like LOWER(:queryString)')
            ->setParameter('queryString', "%$userQueryString%")
            ->orderBy('f.name');

        return $qb->getQuery()->getArrayResult();
    }

   /**
     * Get funder information for the aggregations.
     *
     * @param array $aggregations Aggregations for each funder id.
     *
     * @return array
     */
    public function getFunderInfo(array $aggregations): array
    {
        $funderInfo = array();

        $funders = $this->findBy(array('id' => array_keys($aggregations)));

        foreach ($funders as $funder) {
            $funderInfo[$funder->getId()] = array(
                'id' => $funder->getId(),
                'name' => $funder->getName(),
                'shortName' => $funder->getShortName(),
                'count' => $aggregations[$funder->getId()]
            );
        }
        // Sort
        $array_column = array_column($funderInfo, 'count');
        array_multisort($array_column, SORT_DESC, $funderInfo);
        return $funderInfo;
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

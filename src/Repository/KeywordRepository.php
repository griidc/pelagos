<?php

namespace App\Repository;

use App\Entity\Keyword;
use App\Enum\KeywordType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Keyword>
 *
 * @method Keyword|null find($id, $lockMode = null, $lockVersion = null)
 * @method Keyword|null findOneBy(array $criteria, array $orderBy = null)
 * @method Keyword[]    findAll()
 * @method Keyword[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class KeywordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Keyword::class);
    }

    public function save(Keyword $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Keyword $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getKeywords(?KeywordType $keywordType)
    {
        $qb = $this->createQueryBuilder('k');

        $qb
        ->where('k.type = :type')
        ->andWhere($qb->expr()->notLike('k.displayPath', ':path'))
        ->orWhere('k.label = :science')
        ->setParameter('path', '%EARTH SCIENCE SERVICES%')
        ->setParameter('science', 'Science Keywords')
        ->setParameter('type', $keywordType)
        ;

        return $qb->getQuery()
        ->getResult();
    }

//    /**
//     * @return Keyword[] Returns an array of Keyword objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('k')
//            ->andWhere('k.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('k.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Keyword
//    {
//        return $this->createQueryBuilder('k')
//            ->andWhere('k.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

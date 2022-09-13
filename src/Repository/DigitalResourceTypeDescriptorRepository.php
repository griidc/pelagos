<?php

namespace App\Repository;

use App\Entity\DigitalResourceTypeDescriptor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DigitalResourceTypeDescriptor|null find($id, $lockMode = null, $lockVersion = null)
 * @method DigitalResourceTypeDescriptor|null findOneBy(array $criteria, array $orderBy = null)
 * @method DigitalResourceTypeDescriptor[]    findAll()
 * @method DigitalResourceTypeDescriptor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DigitalResourceTypeDescriptorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DigitalResourceTypeDescriptor::class);
    }

    /**
     * Get Digital Resource Type Descriptors information for the aggregations.
     *
     * @param array $aggregations Aggregations for each id.
     *
     * @return array
     */
    public function getDigitalResourceTypeDescriptorsInfo(array $aggregations): array
    {
        $digitalResourceTypeDescriptorsInfo = array();

        $digitalResourceTypeDescriptors = $this->findBy(array('id' => array_keys($aggregations)));

        foreach ($digitalResourceTypeDescriptors as $digitalResourceTypeDescriptor) {
            $digitalResourceTypeDescriptorsInfo[$digitalResourceTypeDescriptor->getId()] = array(
                'id' => $digitalResourceTypeDescriptor->getId(),
                'name' => $digitalResourceTypeDescriptor->getDescription(),
                'count' => $aggregations[$digitalResourceTypeDescriptor->getId()]
            );
        }

        //Sorting based on highest count
        $array_column = array_column($digitalResourceTypeDescriptorsInfo, 'count');
        array_multisort($array_column, SORT_DESC, $digitalResourceTypeDescriptorsInfo);

        return $digitalResourceTypeDescriptorsInfo;
    }

    // /**
    //  * @return DigitalResourceTypeDescriptor[] Returns an array of DigitalResourceTypeDescriptor objects
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
    public function findOneBySomeField($value): ?DigitalResourceTypeDescriptor
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

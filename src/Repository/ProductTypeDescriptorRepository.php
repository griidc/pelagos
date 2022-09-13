<?php

namespace App\Repository;

use App\Entity\ProductTypeDescriptor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductTypeDescriptor|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductTypeDescriptor|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductTypeDescriptor[]    findAll()
 * @method ProductTypeDescriptor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductTypeDescriptorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductTypeDescriptor::class);
    }

    /**
     * Get Product Type Descriptors information for the aggregations.
     *
     * @param array $aggregations Aggregations for each id.
     *
     * @return array
     */
    public function getProductTypeDescriptorInfo(array $aggregations): array
    {
        $productTypeDescriptorInfo = array();

        $productTypeDescriptors = $this->findBy(array('id' => array_keys($aggregations)));

        foreach ($productTypeDescriptors as $productTypeDescriptor) {
            $productTypeDescriptorInfo[$productTypeDescriptor->getId()] = array(
                'id' => $productTypeDescriptor->getId(),
                'name' => $productTypeDescriptor->getDescription(),
                'count' => $aggregations[$productTypeDescriptor->getId()]
            );
        }

        //Sorting based on highest count
        $array_column = array_column($productTypeDescriptorInfo, 'count');
        array_multisort($array_column, SORT_DESC, $productTypeDescriptorInfo);

        return $productTypeDescriptorInfo;
    }

    // /**
    //  * @return ProductTypeDescriptor[] Returns an array of ProductTypeDescriptor objects
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
    public function findOneBySomeField($value): ?ProductType
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

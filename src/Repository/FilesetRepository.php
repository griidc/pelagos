<?php

namespace App\Repository;

use App\Entity\Fileset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Fileset Entity Repository class.
 *
 * @method Fileset|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fileset|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fileset[]    findAll()
 * @method Fileset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FilesetRepository extends ServiceEntityRepository
{
    /**
     * FilesetRepository constructor.
     *
     * @param ManagerRegistry $registry Register class instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fileset::class);
    }
}

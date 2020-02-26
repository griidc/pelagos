<?php

namespace App\Repository;

use App\Entity\Udi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Udi Entity Repository class.
 */
class UdiRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry The Registry Manager.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Udi::class);
    }
}

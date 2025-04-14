<?php

namespace App\Repository;

use App\Entity\DeletedUdi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * DeletedUdi Entity Repository class.
 *
 * @extends ServiceEntityRepository
 */
class DeletedUdiRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry The Registry Manager.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeletedUdi::class);
    }
}

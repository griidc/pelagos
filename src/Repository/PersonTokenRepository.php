<?php

namespace App\Repository;

use App\Entity\PersonToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Person Entity Repository class.
 */
class PersonTokenRepository extends ServiceEntityRepository
{
    /**
     * PersonTokenRepository constructor.
     *
     * @param ManagerRegistry $registry Register class instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonToken::class);
    }
}
<?php

namespace App\Repository;

use App\Entity\Fileset;
use App\Entity\FundingOrganization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Funding Organization Entity Repository class.
 *
 * @method FundingOrganization|null find($id, $lockMode = null, $lockVersion = null)
 * @method FundingOrganization|null findOneBy(array $criteria, array $orderBy = null)
 * @method FundingOrganization[]    findAll()
 * @method FundingOrganization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FundingOrganizationRepository extends ServiceEntityRepository
{
    /**
     * FilesetRepository constructor.
     *
     * @param ManagerRegistry $registry Register class instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FundingOrganization::class);
    }
}

<?php

namespace App\Repository;

use App\Entity\Fileset;
use App\Entity\FundingOrganization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Funding Organization Entity Repository class.
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

    /**
     * Get funding org information for the aggregations.
     *
     * @param array $aggregations Aggregations for each funding org id.
     *
     * @return array
     */
    public function getFundingOrgInfo(array $aggregations): array
    {
        $fundingOrgInfo = array();

        $fundingOrgs = $this->findBy(array('id' => array_keys($aggregations)));

        foreach ($fundingOrgs as $fundingOrg) {
            $fundingOrgInfo[$fundingOrg->getId()] = array(
                'id' => $fundingOrg->getId(),
                'name' => $fundingOrg->getName(),
                'shortName' => $fundingOrg->getShortName(),
                'count' => $aggregations[$fundingOrg->getId()]
            );
        }
        //Sorting based on highest count
        $array_column = array_column($fundingOrgInfo, 'count');
        array_multisort($array_column, SORT_DESC, $fundingOrgInfo);

        return $fundingOrgInfo;
    }
}

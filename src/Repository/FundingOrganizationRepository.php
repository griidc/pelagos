<?php

namespace App\Repository;

use App\Entity\FundingOrganization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Funding Organization Entity Repository class.
 *
* @extends ServiceEntityRepository<FundingOrganization>
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

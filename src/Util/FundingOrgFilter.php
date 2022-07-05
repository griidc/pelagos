<?php

namespace App\Util;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\FundingOrganization;
use App\Entity\Dataset;

/**
 * A utility to determine if and which Funding Organizations need to be filtered by.
 */
class FundingOrgFilter
{
    /**
     * The entity manager to use.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * An array of the funding organization to filter by.
     *
     * @var array
     */
    protected $fundingOrganizations;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager The entity manager to use.
     * @param array                  $fundingOrgs   Funding Organizations to filter by.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        array $fundingOrgs
    ) {
        $this->entityManager = $entityManager;

        $this->fundingOrganizations = $this->entityManager
            ->getRepository(FundingOrganization::class)
            ->findBy(array('shortName' => $fundingOrgs));
    }

    /**
     * Returns true is you need to filter by Funding Organization.
     *
     * @return bool Is the filter active?
     */
    public function isActive(): bool
    {
        return !empty($this->fundingOrganizations);
    }

    /**
     * Returns true is you need to filter by Funding Organization.
     *
     * @return array List of ID's to filter on.
     */
    public function getFilterIdArray(): array
    {
        $ids = array();
        foreach ($this->fundingOrganizations as $fundingOrganization) {
            $ids[] = $fundingOrganization->getId();
        }

        return $ids;
    }

    /**
     * Returns true is you need to filter by Funding Organization.
     *
     * @return array List of ID's to filter on.
     */
    public function getResearchGroupsIdArray(): array
    {
        $ids = array();
        foreach ($this->fundingOrganizations as $fundingOrganization) {
            foreach ($fundingOrganization->getFundingCycles() as $fundingCycle) {
                foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
                    $ids[] = $researchGroup->getId();
                }
            }
        }

        return $ids;
    }

    /**
     * Returns true if passed dataset is in funding organization filter list.
     *
     * @param \App\Entity\Dataset $dataset The dataset to be indexed.
     *
     * @return boolean If the dataset is in the filter array.
     */
    public static function canIndex(Dataset $dataset): bool
    {
        $filterEnv = $_ENV['FILTER_BY_FUNDING_ORG'];
        $shortNameArray = JSON_DECODE($filterEnv);
        if (empty($shortNameArray)) {
            return true;
        }
        $fundingOrgShortName = $dataset->getResearchGroup()->getFundingCycle()->getFundingOrganization()->getShortName();
        return in_array($fundingOrgShortName, $shortNameArray);
    }
}

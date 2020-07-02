<?php

namespace App\Util;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\FundingOrganization;

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
    public function isActive() :bool
    {
        return !empty($this->fundingOrganizations);
    }

    /**
     * Returns true is you need to filter by Funding Organization.
     *
     * @return array List of ID's to filter on.
     */
    public function getFilterIdArray() :array
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
    public function getResearchGroupsIdArray() :array
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
     * Return the list of funding organizations.
     *
     * @return array
     */
    public function getFundingOrganizations(): array
    {
        return $this->fundingOrganizations;
    }
}

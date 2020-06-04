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

        $this->shortNamesToIdArray($fundingOrgs);
    }

     /**
     * Set array of FOs to filter by.
     *
     * @param array $fundingOrgs List of Funding Organizations Short Names.
     *
     * @return void
     */
    private function shortNamesToIdArray(array $fundingOrgs)
    {
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
}

<?php

namespace App\Util;

/**
 * A utility to create and issue DOI from Datacite REST API.
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
     * @var EntityManagerInterface
     */
    protected $fundingOrgs;
    
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
        $this->fundingOrgs = $fundingOrgs;
    } 
}
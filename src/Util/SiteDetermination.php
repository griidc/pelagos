<?php

namespace App\Util;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\FundingOrganization;
use App\Entity\Dataset;

/**
 * A utility to determine site is being used, and what how to filter.
 */
class SiteDetermination
{
    /**
     * The configuration of sites, templates and filtering.
     *
     * @var array
     */
    protected $siteConfig;
    
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager The entity manager to use.
     * @param array                  $fundingOrgs   Funding Organizations to filter by.
     */
    public function __construct(array $siteConfig) 
    {
        dump($siteConfig);
        if (array_key_exists("default", $siteConfig) and !empty($siteConfig["default"])) {
            $config = $siteConfig["default"];
        } else {
            $config = strtoupper(explode('.', gethostname()))[0];
            // Make sure the sub domain is in the array.
        }
        
        $this->siteConfig = $siteConfig[$config];
    }
    
    public function getBaseTemplate()
    {
        return $this->siteConfig["baseTemplate"];
    }
    
    public function getFundingOrganizations()
    {
        return $this->siteConfig["filterFundingOrgBy"];
    }
    
    
    
    
    
    
    
    
    
}
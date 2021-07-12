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
     * The name to the determinted config.
     *
     * @var string
     */
    protected $configName;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager The entity manager to use.
     * @param array                  $fundingOrgs   Funding Organizations to filter by.
     */
    public function __construct(array $siteConfig)
    {
        if (array_key_exists("default", $siteConfig) and !empty($siteConfig["default"])) {
            $config = $siteConfig["default"];
        } else {
            $subDomain = strtoupper(explode('.', gethostname())[0]);
            if (array_key_exists($subDomain, $siteConfig)) {
                $config = $subDomain;
            } else {
                $config = strtoupper(array_key_first($siteConfig));
            }
        }

        $this->configName = $config;
        $this->siteConfig = $siteConfig[$config];
    }

    /**
     * Will return the base path file name.
     *
     * @var string The name is determinted config.
     */
    public function getConfigName() :string
    {
        return $this->configName;
    }

    /**
     * Will return the base path file name.
     *
     * @var string The base template file path name.
     */
    public function getBaseTemplate() :string
    {
        return $this->siteConfig["baseTemplate"];
    }

    /**
     * Will return a list of funding organization IDs.
     *
     * @return array A list of funding organization IDs.
     */
    public function getFundingOrganizations() :array
    {
        return $this->siteConfig["filterFundingOrgBy"];
    }
}

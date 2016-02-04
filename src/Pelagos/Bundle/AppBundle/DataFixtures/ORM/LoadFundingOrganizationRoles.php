<?php

namespace Pelagos\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pelagos\Entity\FundingOrganizationRole;

/**
 * Fixture to load the standard Funding Organization Roles.
 */
class LoadFundingOrganizationRoles implements FixtureInterface
{
    /**
     * Load data fixtures for Funding Organization Roles.
     *
     * @param ObjectManager $entityManager The entity manager to use.
     *
     * @return void
     */
    public function load(ObjectManager $entityManager)
    {
        $fundingOrganizationRoles = array(
            array(
                'name' => 'Leadership',
                'weight' => 1,
            ),
            array(
                'name' => 'Advisory',
                'weight' => 2,
            ),
            array(
                'name' => 'Admin',
                'weight' => 3,
            ),
        );
        foreach ($fundingOrganizationRoles as $fundingOrganizationRole) {
            $entity = new FundingOrganizationRole();
            $entity->setName($fundingOrganizationRole['name']);
            $entity->setWeight($fundingOrganizationRole['weight']);
            $entity->setCreator('pelagos');
            $entityManager->persist($entity);
        }
        $entityManager->flush();
    }
}

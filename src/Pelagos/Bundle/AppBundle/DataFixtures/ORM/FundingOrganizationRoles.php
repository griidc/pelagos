<?php

namespace Pelagos\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pelagos\Entity\FundingOrganizationRole;

/**
 * Fixture to load the standard Funding Organization Roles.
 *
 * These roles were defined in PELAGOS-1596.
 */
class FundingOrganizationRoles implements FixtureInterface
{
    const LEADERSHIP = 'Leadership';
    const ADVISORY = 'Advisory';
    const ADMIN = 'Admin';

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
                'name' => self::LEADERSHIP,
                'weight' => 1,
            ),
            array(
                'name' => self::ADVISORY,
                'weight' => 2,
            ),
            array(
                'name' => self::ADMIN,
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

<?php

namespace Pelagos\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Pelagos\Entity\FundingOrganizationRole;
use Pelagos\Entity\Person;

/**
 * Fixture to load the standard Funding Organization Roles.
 *
 * These roles were defined in PELAGOS-1596.
 */
class FundingOrganizationRoles extends AbstractFixture implements OrderedFixtureInterface
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
        $systemPerson = $entityManager->find(Person::class, 0);
        foreach ($fundingOrganizationRoles as $fundingOrganizationRole) {
            $entity = new FundingOrganizationRole();
            $entity->setName($fundingOrganizationRole['name']);
            $entity->setWeight($fundingOrganizationRole['weight']);
            $entity->setCreator($systemPerson);
            $entityManager->persist($entity);
        }
        $entityManager->flush();
    }

    /**
     * The order this fixture should be run in.
     *
     * @return integer
     */
    public function getOrder()
    {
        return 2;
    }
}

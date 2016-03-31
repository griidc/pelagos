<?php

namespace Pelagos\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Pelagos\Entity\ResearchGroupRole;
use Pelagos\Entity\Person;

/**
 * Fixture to load the standard Research Group Roles.
 *
 * These roles were defined in PELAGOS-1588.
 */
class ResearchGroupRoles extends AbstractFixture implements OrderedFixtureInterface
{
    const LEADERSHIP = 'Leadership';
    const ADMIN = 'Administration';
    const DATA = 'Data';
    const RESEARCHER = 'Researcher';

    /**
     * Load data fixtures for Research Group Roles.
     *
     * @param ObjectManager $entityManager The entity manager to use.
     *
     * @return void
     */
    public function load(ObjectManager $entityManager)
    {
        $researchGroupRoles = array(
            array(
                'name' => self::LEADERSHIP,
                'weight' => 1,
            ),
            array(
                'name' => self::ADMIN,
                'weight' => 2,
            ),
            array(
                'name' => self::DATA,
                'weight' => 3,
            ),
            array(
                'name' => self::RESEARCHER,
                'weight' => 4,
            ),
        );
        $systemPerson = $entityManager->find(Person::class, 0);
        foreach ($researchGroupRoles as $researchGroupRole) {
            $entity = new ResearchGroupRole();
            $entity->setName($researchGroupRole['name']);
            $entity->setWeight($researchGroupRole['weight']);
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
        return 3;
    }
}

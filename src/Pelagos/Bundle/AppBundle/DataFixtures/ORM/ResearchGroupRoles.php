<?php

namespace Pelagos\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pelagos\Entity\ResearchGroupRole;

/**
 * Fixture to load the standard Research Group Roles.
 *
 * These roles were defined in PELAGOS-1588.
 */
class ResearchGroupRoles implements FixtureInterface
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
        foreach ($researchGroupRoles as $researchGroupRole) {
            $entity = new ResearchGroupRole();
            $entity->setName($researchGroupRole['name']);
            $entity->setWeight($researchGroupRole['weight']);
            $entity->setCreator('pelagos');
            $entityManager->persist($entity);
        }
        $entityManager->flush();
    }
}

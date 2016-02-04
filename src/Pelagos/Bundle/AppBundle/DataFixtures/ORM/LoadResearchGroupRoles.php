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
class LoadResearchGroupRoles implements FixtureInterface
{
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
                'name' => 'Leadership',
                'weight' => 1,
            ),
            array(
                'name' => 'Administration',
                'weight' => 2,
            ),
            array(
                'name' => 'Data',
                'weight' => 3,
            ),
            array(
                'name' => 'Researcher',
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

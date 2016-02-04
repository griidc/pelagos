<?php

namespace Pelagos\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pelagos\Entity\DataRepositoryRole;

/**
 * Fixture to load the standard Data Repository Roles.
 */
class LoadDataRepositoryRoles implements FixtureInterface
{
    /**
     * Load data fixtures for Data Repository Roles.
     *
     * @param ObjectManager $entityManager The entity manager to use.
     *
     * @return void
     */
    public function load(ObjectManager $entityManager)
    {
        $dataRepositoryRoles = array(
            array(
                'name' => 'Manager',
                'weight' => 1,
            ),
            array(
                'name' => 'Developer',
                'weight' => 2,
            ),
            array(
                'name' => 'Support',
                'weight' => 3,
            ),
            array(
                'name' => 'Subject Matter Expert',
                'weight' => 4,
            ),
        );
        foreach ($dataRepositoryRoles as $dataRepositoryRole) {
            $entity = new DataRepositoryRole();
            $entity->setName($dataRepositoryRole['name']);
            $entity->setWeight($dataRepositoryRole['weight']);
            $entity->setCreator('pelagos');
            $entityManager->persist($entity);
        }
        $entityManager->flush();
    }
}

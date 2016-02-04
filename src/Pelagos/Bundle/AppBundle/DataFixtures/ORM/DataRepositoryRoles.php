<?php

namespace Pelagos\Bundle\AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pelagos\Entity\DataRepositoryRole;

/**
 * Fixture to load the standard Data Repository Roles.
 *
 * These roles were defined in PELAGOS-1678.
 */
class DataRepositoryRoles implements FixtureInterface
{
    const MANAGER = 'Manager';
    const ENGINEER = 'Developer';
    const SUPPORT = 'Support';
    const SME = 'Subject Matter Expert';

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
                'name' => self::MANAGER,
                'weight' => 1,
            ),
            array(
                'name' => self::ENGINEER,
                'weight' => 2,
            ),
            array(
                'name' => self::SUPPORT,
                'weight' => 3,
            ),
            array(
                'name' => self::SME,
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

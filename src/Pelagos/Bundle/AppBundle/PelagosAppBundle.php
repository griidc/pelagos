<?php

namespace Pelagos\Bundle\AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\DBAL\Types\Type;

/**
 * A bundle for the Pelagos application.
 */
class PelagosAppBundle extends Bundle
{
    /**
     * Boots the bundle.
     *
     * @return void
     */
    public function boot()
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        // If the custom type "interval" has not already been added to the Doctrine type map.
        if (!Type::hasType('interval')) {
            // Add the mapping.
            Type::addType('interval', 'Pelagos\DoctrineExtensions\DBAL\Types\IntervalType');
        }
        // Register interval for use with our database platform.
        $entityManager
            ->getConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping(
                // Database type
                'interval',
                // Doctrine type
                'interval'
            );

        // If the custom type "citext" has not already been added to the Doctrine type map.
        if (!Type::hasType('citext')) {
            // Add the mapping.
            Type::addType('citext', 'Pelagos\DoctrineExtensions\DBAL\Types\CITextType');
        }
        // Register citext for use with our database platform.
        $entityManager
            ->getConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping(
                // Database type
                'citext',
                // Doctrine type
                'citext'
            );

        // Force session creation.
        $this->container->get('session')->count();
    }
}

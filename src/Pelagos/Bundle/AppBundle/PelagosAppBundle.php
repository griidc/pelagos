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

        // If the custom type "token_use_type" has not already been added to the Doctrine type map.
        if (!Type::hasType('token_use_type')) {
            // Add the mapping.
            Type::addType('token_use_type', 'Pelagos\DoctrineExtensions\DBAL\Types\TokenUseType');
        }
        // Register token_use_type for use with our database platform.
        $entityManager
            ->getConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping(
                // Database type
                'token_use_type',
                // Doctrine type
                'token_use_type'
            );

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
    }
}

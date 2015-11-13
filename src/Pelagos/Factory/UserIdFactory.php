<?php

namespace Pelagos\Factory;

use Pelagos\Entity\Person;
use Pelagos\Service\EntityService;

/**
 * A factory class for generating user IDs.
 */
class UserIdFactory
{
    /**
     * Private constructor to prevent instantiation.
     */
    private function __construct()
    {
        // Do nothing.
    }

    /**
     * Generate a unique user ID for a Person.
     *
     * @param Person        $person        The Person to generate a user ID for.
     * @param EntityService $entityService An instance of the EntityService.
     *
     * @return string A unique user ID for $person.
     */
    public static function generateUniqueUserId(Person $person, EntityService $entityService)
    {
        // Sanitize Person's first name.
        $sanitizedFirstName
            = preg_replace(
                // Remove any remaining invalid characters.
                '/[^a-z0-9]/',
                '',
                transliterator_transliterate(
                    // Convert all characters in last name to latin, then to their ascii equivalent, then to lower case.
                    'Any-Latin; Latin-ASCII; Lower()',
                    $person->getFirstName()
                )
            );
        // Sanitize Person's last name.
        $sanitizedLastName
            = preg_replace(
                // Remove any remaining invalid characters.
                '/[^a-z0-9]/',
                '',
                transliterator_transliterate(
                    // Convert all characters in last name to latin, then to their ascii equivalent, then to lower case.
                    'Any-Latin; Latin-ASCII; Lower()',
                    $person->getLastName()
                )
            );
        // Construct candidate user ID from first character of sanitized first name and sanitized last name.
        $candidateUserId = substr($sanitizedFirstName, 0, 1) . $sanitizedLastName;
        // Truncate to 32 characters.
        $candidateUserId = substr($candidateUserId, 0, 32);
        // Get all existing user IDs.
        $userIds = $entityService->getDistinctVals('Account', 'userId');
        // Initialize $userId with our candidate user ID.
        $userId = $candidateUserId;
        // Start our uniquifier at 2.
        $uniquifier = 2;
        // Loop and check if $userId is unique.
        while (in_array($userId, $userIds)) {
            // If $userId is not unique, append the uniquifier.
            $userId = $candidateUserId . $uniquifier;
            // Increment the uniquifier for next iteration.
            $uniquifier++;
        }
        // Return unique user ID.
        return $userId;
    }
}

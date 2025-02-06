<?php

namespace App\Security;

use App\Entity\Account;
use App\Entity\PersonToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * A User Provider for Person Tokens.
 *
 * @see UserProviderInterface
 */
class PersonTokenUserProvider implements UserProviderInterface
{
    /**
     * An instance of the Doctrine entity manager.
     *
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Constructor that intitializes the entity manager.
     *
     * @param EntityManagerInterface $entityManager An instance of the Doctrine entity manager.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Load by username.
     *
     * @deprecated Remove this in Symfony 6. Use loadUserByIdentifier instead.
     *
     * @param string $username
     * @return UserInterface
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * Load the Account for a given token string or create a temporary one.
     *
     * @param string $identifier The token string to load the Account for.
     *
     * @throws AuthenticationCredentialsNotFoundException When the provided token string does not match
     *                                                    any existing token.
     * @throws \Exception                                 When more than one token is found for the provided
     *                                                    token string (this is never supposed to happen).
     * @throws AuthenticationExpiredException             When the provided token string is for an expired token.
     *
     * @return Account The account for the given token string.
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $personTokens = $this->entityManager->getRepository(PersonToken::class)->findBy(
            array('tokenText' => $identifier)
        );
        if (count($personTokens) === 0) {
            throw new AuthenticationCredentialsNotFoundException();
        }
        if (count($personTokens) > 1) {
            throw new \Exception(
                sprintf('Multiple tokens found for token string: "%s"', $identifier)
            );
        }
        $personToken = $personTokens[0];
        if (!$personToken->isValid()) {
            throw new AuthenticationExpiredException();
        }
        $person = $personToken->getPerson();
        $account = $person?->getAccount();
        if ($account instanceof Account) {
            return $account;
        }
        return new Account($person, $person?->getEmailAddress());
    }

    /**
     * This is used for storing authentication in the session.
     *
     * @param UserInterface $user The user object.
     *
     * @throws UnsupportedUserException Always.
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        // We send the token in each request so authentication is stateless.
        // Throwing this exception is proper to make things stateless.
        throw new UnsupportedUserException();
    }

    /**
     * Report whether this provider supports a class.
     *
     * @param string $class The class to test.
     */
    // Next line to be ignored because implemented function does not have type-hint on $class.
    // phpcs:ignore
    public function supportsClass(string $class): bool
    {
        return Account::class === $class;
    }
}

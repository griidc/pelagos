<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use App\Exception\PasswordException;

/**
 * Entity class to represent a Password.
 *
 * This class defines an Password, which consists of a type, hash, and salt.
 */
#[ORM\Entity]
class Password extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Password';

    /**
     * Account this Password is attached to.
     *
     * @var Account
     *
     *
     *
     * @Assert\NotBlank(
     *     message="A Password must be attached to an Account"
     * )
     */
    #[ORM\ManyToOne(targetEntity: 'Account', inversedBy: 'passwordHistory')]
    #[ORM\JoinColumn(referencedColumnName: 'person_id')]
    protected $account;

    /**
     * A binary string containing the hashed password.
     *
     * @var string
     *
     *
     * @Assert\NotBlank(
     *     message="Password hash is required"
     * )
     */
    #[ORM\Column(type: 'blob')]
    #[Serializer\Exclude]
    protected $passwordHash;

    /**
     * A string containing the cleartext password.
     *
     * This field is to only be used for comparison
     * against existing hashes after salting and hashing
     * its contents, and for populating OpenLDAP.
     * THIS FIELD SHALL NOT BE PERSISTED.
     *
     * @var string
     */
    #[Serializer\Exclude]
    protected $clearTextPassword;

    /**
     * The algorithm used to hash the password.
     *
     * @var string
     *
     *
     * @Assert\NotBlank(
     *     message="Password hash algorithm is required"
     * )
     */
    #[ORM\Column(type: 'text')]
    #[Serializer\Exclude]
    protected $passwordHashAlgorithm;

    /**
     * A binary string containing the salt used when hashing the password.
     *
     * @var string
     *
     *
     * @Assert\NotBlank(
     *     message="Password hash salt is required"
     * )
     */
    #[ORM\Column(type: 'blob')]
    #[Serializer\Exclude]
    protected $passwordHashSalt;

    /**
     * Constructor for Password.
     *
     * @param string $passwordText The password text for this account.
     */
    public function __construct(string $passwordText)
    {
        $this->setPassword($passwordText);
    }

    /**
     * Attach an Account to this password.
     *
     * @param Account $account The account to attach to this password.
     *
     * @return void
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
    }

    /**
     * Get the never-to-be-persisted clear text password.
     *
     * @return string The clear text password.
     */
    public function getClearTextPassword()
    {
        return $this->clearTextPassword;
    }

    /**
     * Get the Account this password is attached to.
     *
     * @return Account The Account this password is attached to.
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set the password attributes for a provided plain text password.
     *
     * @param string $password Plain text password.
     *
     * @throws PasswordException When $password is shorter than 8 characters.
     * @throws PasswordException When $password does not meet complexity requirements.
     * @throws \Exception        When unable to generate a cryptographically strong password hash salt.
     *
     * @return void
     */
    public function setPassword(string $password)
    {
        if (strlen($password) < 8) {
            throw new PasswordException('Password is not long enough (must be at least 8 characters)');
        }

        $passwordComplexityRegEx
            = '/^' .
            // Password must contain:
            '(?:' .
                // a digit, a lowercase letter, and an uppercase letter
                '(?:(?=.*\d)(?=.*\p{Ll})(?=.*\p{Lu}))' .
                // or
                '|' .
                // a digit, a lowercase letter, and a character that is not a digit or cased letter
                '(?:(?=.*\d)(?=.*\p{Ll})(?=.*[^\d\p{Ll}\p{Lu}]))' .
                // or
                '|' .
                // a digit, an uppercase letter, and a character that is not a digit or cased letter
                '(?:(?=.*\d)(?=.*\p{Lu})(?=.*[^\d\p{Ll}\p{Lu}]))' .
                // or
                '|' .
                // a lowercase letter, an uppercase letter, and a character that is not a digit or cased letter
                '(?:(?=.*\p{Ll})(?=.*\p{Lu})(?=.*[^\d\p{Ll}\p{Lu}]))' .
            ')' .
            // and can contain any other characters as long as the above matches.
            '.+$/';

        if (!preg_match($passwordComplexityRegEx, $password)) {
            throw new PasswordException('Password is not complex enough');
        }

        $this->passwordHashAlgorithm = 'SSHA';
        // Assume the salt is not crptographically strong by default.
        $cryptoStrongSalt = false;
        // Attempt to generate a cryptographically strong 4 byte random salt.
        $this->passwordHashSalt = openssl_random_pseudo_bytes(4, $cryptoStrongSalt);
        // If the generate salt is not cryptographically strong.
        if (!$cryptoStrongSalt) {
            throw new \Exception('Could not generate a cryptographically strong password hash salt');
        }
        // Append the salt to the password, hash it, and save the hash.
        $this->passwordHash = sha1($password . $this->passwordHashSalt, true);
        // set cleartext password
        $this->clearTextPassword = $password;
    }

    /**
     * Compare a plain text password against the hashed password.
     *
     * @param string $password Plain text password.
     *
     * @return boolean Whether or not the provided password matches the hash.
     */
    public function comparePassword(string $password)
    {
        $hash = sha1($password . $this->getSalt(), true);
        if ($hash === $this->getPasswordHash()) {
            return true;
        }
        return false;
    }

    /**
     * Returns the passwordHashSalt for this Account.
     *
     * @return string The passwordHashSalt for this Account.
     */
    public function getSalt()
    {
        if (is_resource($this->passwordHashSalt)) {
            return stream_get_contents($this->passwordHashSalt);
        }
        return $this->passwordHashSalt;
    }

    /**
     * Returns the passwordHash for this Account.
     *
     * @return string The passwordHash for this Account.
     */
    public function getPasswordHash()
    {
        if (is_resource($this->passwordHash)) {
            return stream_get_contents($this->passwordHash);
        }
        return $this->passwordHash;
    }

    /**
     * Returns the hashing algorithm used to generate the password hash.
     *
     * @return string The hashing algorithm.
     */
    public function getHashAlgorithm()
    {
        return $this->passwordHashAlgorithm;
    }

    /**
     * Return base64 encoded salted password hash.
     */
    public function getSSHAPassword(): string
    {
        return "{SSHA}" . base64_encode(pack('H*', bin2hex($this->getPasswordHash()) . bin2hex($this->getSalt())));
    }
}

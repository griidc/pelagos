<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use App\Exception\PasswordException;

/**
 * Entity class to represent an Account.
 *
 * This class defines an Account, which is a set of credentials for a Person.
 *
 * @ORM\Entity
 */
class Account extends Entity implements UserInterface, EquatableInterface
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Account';

    /**
     * The standard role given to all users.
     */
    const ROLE_USER = 'ROLE_USER';

    /**
     * A role given only to Data Repository Managers.
     */
    const ROLE_DATA_REPOSITORY_MANAGER = 'ROLE_DATA_REPOSITORY_MANAGER';

    /**
     * A role given only to Research Group Data people.
     */
    const ROLE_RESEARCH_GROUP_DATA = 'ROLE_RESEARCH_GROUP_DATA';

    /**
     * A role given only to Subject matter experts.
     */
    const ROLE_SUBJECT_MATTER_EXPERT = 'ROLE_SUBJECT_MATTER_EXPERT';

    /**
     * This is defined here to override the base class id.
     *
     * This is not used by the Account Entity because it gets its identity through Person.
     *
     * @var null
     */
    protected $id;

    /**
     * Person this account is attached to.
     *
     * @var Person
     *
     * @ORM\OneToOne(targetEntity="Person", inversedBy="account")
     * @ORM\Id
     *
     * @Assert\NotBlank(
     *     message="An account must be attached to a Person"
     * )
     */
    protected $person;

    /**
     * User's ID.
     *
     * @var string
     *
     * @ORM\Column(type="citext", unique=true)
     *
     * @Assert\NotBlank(
     *     message="User ID is required"
     * )
     */
    protected $userId;

    /**
     * Current Password object associated with Account.
     *
     * @var Password
     *
     * @ORM\OneToOne(targetEntity="Password", cascade={"persist"})
     *
     * @Assert\NotBlank(
     *     message="An Account must be attached to a Password"
     * )
     */
    protected $password;

    /**
     * Historical Password objects associated with Account.
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="Password", mappedBy="account", fetch="EXTRA_LAZY")
     *
     * @ORM\OrderBy({"modificationTimeStamp"="DESC"})
     */
    protected $passwordHistory;

    /**
     * Login attempts for this account.
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="LoginAttempts", mappedBy="account", fetch="EXTRA_LAZY")
     *
     * @ORM\OrderBy({"creationTimeStamp"="DESC"})
     */
    protected $loginAttempts;

    /**
     * Whether this Account is a POSIX account.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    protected $posix = false;

    /**
     * The uid number for this Account.
     *
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true, unique=true)
     */
    protected $uidNumber;

    /**
     * The gid number for this Account.
     *
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $gidNumber;

    /**
     * The home directory for this Account.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $homeDirectory;

    /**
     * The login shell for this Account.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $loginShell;

    /**
     * SSH public keys for this account.
     *
     * @var array
     *
     * @ORM\Column(type="json", nullable=true)
     */
    protected $sshPublicKeys = array();

    /**
     * Constructor for Account.
     *
     * @param Person   $person   The Person this account is for.
     * @param string   $userId   The user ID for this account.
     * @param Password $password The password for this account.
     */
    public function __construct(Person $person = null, string $userId = null, Password $password = null)
    {
        $this->passwordHistory = new ArrayCollection();
        $this->loginAttempts = new ArrayCollection();
        if ($person !== null) {
            $this->setPerson($person);
        }
        if ($userId !== null) {
            $this->setUserId($userId);
        }
        if ($password !== null) {
            $this->setPassword($password);
        }
    }

    /**
     * Override Account's getId() method with Person's Id.
     *
     * @return The EntityID of the Person associated with this Account.
     */
    public function getId()
    {
        if ($this->getPerson() instanceof Person) {
            return $this->getPerson()->getId();
        }
    }

    /**
     * Attach a Person to this account.
     *
     * @param Person $person The person to attach to this account.
     *
     * @return void
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;
        if ($this->person !== null and $this->person->getAccount() !== $this) {
            $this->person->setAccount($this);
        }
    }

    /**
     * Get the Person this account is attached to.
     *
     * @return Person The Person this account is attached to.
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Assign for userId property.
     *
     * @param string $userId User credential user id of a Person.
     *
     * @return void
     */
    public function setUserId(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Retrieve the userId property.
     *
     * @return string Account user id.
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the password attribute with a Password object.
     *
     * @param Password $password   Pelagos password object.
     * @param boolean  $lessStrict If less strict password rules are allowed.
     *
     * @throws PasswordException When password last changed within 24 hrs.
     * @throws PasswordException When an old password is re-used.
     *
     * @return void
     */
    public function setPassword(Password $password, bool $lessStrict = false)
    {
        $this->password = $password;

        if (false === $lessStrict) {
            // check for minimum age.
            $interval = new \DateInterval('PT24H');
            $now = new \DateTime();
            if (
                !$this->passwordHistory->isEmpty() and
                $this->passwordHistory->first()->getModificationTimeStamp()->add($interval) > $now
            ) {
                throw new PasswordException('This password has already been changed within the last 24 hrs');
            }

            // Throw exception if this password hash is
            // found in last 10 of password history.  The subset of history
            // is provided by a combination of EXTRA_LAZY and the Slice() method.
            $clearText = $this->password->getClearTextPassword();
            foreach ($this->passwordHistory->slice(0, 10) as $oldPasswordObject) {
                $comparisonHash = sha1($clearText . $oldPasswordObject->getSalt(), true);
                if ($comparisonHash === $oldPasswordObject->getPasswordHash()) {
                    throw new PasswordException('This password has already been used');
                }
            }
        }
        $this->password->setAccount($this);
    }

    /**
     * Returns the userId for this Account.
     *
     * This is required by \Symfony\Component\Security\Core\User\UserInterface
     *
     * @return string The userId for this Account.
     */
    public function getUsername()
    {
        return $this->userId;
    }

    /**
     * Get User Indentifier.
     *
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->userId;
    }

    /**
     * Returns the passwordHash contained in the account's Password attribute.
     *
     * This is required by \Symfony\Component\Security\Core\User\UserInterface
     *
     * @return string|null The passwordHash for this Account.
     */
    public function getPassword()
    {
        if ($this->password === null) {
            return null;
        }
        return $this->password->getPasswordHash();
    }

    /**
     * Returns the Password entity attached to this Account.
     *
     * @return Password The Password entity attached to this Account.
     */
    public function getPasswordEntity()
    {
        return $this->password;
    }

    /**
     * Returns the LoginAttempts Collection for this Account.
     *
     * @return Collection The LoginAttempts entity attached to this Account.
     */
    public function getLoginAttempts()
    {
        return $this->loginAttempts;
    }

    /**
     * Whether or not this account is locked out.
     *
     * @return boolean
     */
    public function isLockedOut()
    {
        $lockoutTimeSeconds = 600;
        $maxAttempts = 100;

        $tooManyAttempts = false;
        $timeHasPassed = false;

        $lastAttempt = $this->loginAttempts->first();

        // No previous attemps have been made.
        if (!$lastAttempt instanceof LoginAttempts) {
            return false;
        }

        $lastTimeStamp = $lastAttempt->getCreationTimeStamp()->getTimestamp();

        // Filter only attempts 10 minutes from last attempt.
        $attempts = $this->loginAttempts->filter(
            function ($attempt) use ($lastTimeStamp, $lockoutTimeSeconds) {
                $timeStamp = $attempt->getCreationTimeStamp()->getTimestamp();
                $seconds = ($lastTimeStamp - $timeStamp);

                if ($seconds < $lockoutTimeSeconds) {
                    return true;
                }
            }
        );

        // Check to see if maximum attemps have been exceeded.
        if (count($attempts) >= $maxAttempts) {
            $tooManyAttempts = true;
        }

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $nowTimeStamp = $now->getTimestamp();

        // If lockout time has passed.
        if (($nowTimeStamp - $lastTimeStamp) > $lockoutTimeSeconds) {
            $timeHasPassed = true;
        }

        // If there have been too many attempts, and time has not passed.
        if ($tooManyAttempts and !$timeHasPassed) {
            return true;
        }

        return false;
    }

    /**
     * Make this Account a POSIX account.
     *
     * @param integer     $uidNumber           The uid number for this account.
     * @param integer     $gidNumber           The gid number for this account.
     * @param string      $homeDirectoryPrefix The home dircectory prefix for this account.
     * @param string|null $loginShell          The login shell for this account (default: /sbin/nologin).
     *
     * @throws \Exception When $uidNumber is not an integer.
     * @throws \Exception When $gidNumber is not an integer.
     *
     * @return void
     */
    public function makePosix(int $uidNumber, int $gidNumber, string $homeDirectoryPrefix, string $loginShell = '/sbin/nologin')
    {
        if ('integer' !== gettype($uidNumber)) {
            throw new \Exception("$uidNumber is not as valid uid number (must be an integer)");
        }
        if ('integer' !== gettype($gidNumber)) {
            throw new \Exception("$gidNumber is not as valid gid number (must be an integer)");
        }
        $this->uidNumber = $uidNumber;
        $this->gidNumber = $gidNumber;
        $this->homeDirectory = preg_replace('/\/$/', '', $homeDirectoryPrefix) . '/' . $this->userId;
        $this->loginShell = $loginShell;
        $this->posix = true;
    }

    /**
     * Whether or not this account is POSIX.
     *
     * @return boolean
     */
    public function isPosix()
    {
        return $this->posix;
    }

    /**
     * Get the uid number for this account.
     *
     * @return integer
     */
    public function getUidNumber()
    {
        return $this->uidNumber;
    }

    /**
     * Get the gid number for this account.
     *
     * @return integer
     */
    public function getGidNumber()
    {
        return $this->gidNumber;
    }

    /**
     * Get the home directory for this account.
     *
     * @return string
     */
    public function getHomeDirectory()
    {
        return $this->homeDirectory;
    }

    /**
     * Set the home directory for this account.
     *
     * @param string|null $homeDirectory The home directory.
     *
     * @return void
     */
    public function setHomeDirectory(?string $homeDirectory)
    {
        $this->homeDirectory = $homeDirectory;
    }

    /**
     * Get the login shell for this account.
     *
     * @return string
     */
    public function getLoginShell()
    {
        return $this->loginShell;
    }

    /**
     * Add an SSH public key for this account.
     *
     * @param string $sshPublicKey The SSH public key to add.
     * @param string $keyName      A name for this SSH public key.
     *
     * @return void
     */
    public function addSshPublicKey(string $sshPublicKey, string $keyName)
    {
        $this->sshPublicKeys[$keyName] = $sshPublicKey;
    }

    /**
     * Remove an SSH public key from this account.
     *
     * @param string $keyName The name of the SSH public key to remove.
     *
     * @throws \Exception When the SSH public key referenced by $keyName does not exist.
     *
     * @return void
     */
    public function removeSshPublicKey(string $keyName)
    {
        if (!array_key_exists($number, $this->sshPublicKeys)) {
            throw new \Exception("SSH pubilc key $keyName does not exist");
        }
        unset($this->sshPublicKeys[$keyName]);
    }

    /**
     * Get all SSH public keys for this account.
     *
     * @return array
     */
    public function getSshPublicKeys()
    {
        return $this->sshPublicKeys;
    }

    /**
     * Get the SSH public key for this account referenced by $keyName.
     *
     * @param string $keyName The name of the SSH public key to retrieve.
     *
     * @return string
     */
    public function getSshPublicKey(string $keyName)
    {
        return $this->sshPublicKeys[$keyName];
    }

    /**
     * Returns the roles for this Account.
     *
     * This is required by \Symfony\Component\Security\Core\User\UserInterface
     *
     * @return array The roles for this Account.
     */
    public function getRoles()
    {
        $roles = array(self::ROLE_USER);
        foreach ($this->getPerson()->getPersonDataRepositories() as $personDataRepository) {
            if (
                $personDataRepository->getRole()->getName() == DataRepositoryRole::MANAGER
                and !in_array(self::ROLE_DATA_REPOSITORY_MANAGER, $roles)
            ) {
                $roles[] = self::ROLE_DATA_REPOSITORY_MANAGER;
            } elseif (
                $personDataRepository->getRole()->getName() === DataRepositoryRole::SME
                and !in_array(self::ROLE_SUBJECT_MATTER_EXPERT, $roles)
            ) {
                $roles[] = self::ROLE_SUBJECT_MATTER_EXPERT;
            }
        }
        foreach ($this->getPerson()->getPersonResearchGroups() as $personResearchGroup) {
            if (
                $personResearchGroup->getRole()->getName() == ResearchGroupRole::DATA
                and !in_array(self::ROLE_RESEARCH_GROUP_DATA, $roles)
            ) {
                $roles[] = self::ROLE_RESEARCH_GROUP_DATA;
            }
        }
        return $roles;
    }

    /**
     * Does nothing because aren't keeping the plaintext password in the Account object.
     *
     * This is required by \Symfony\Component\Security\Core\User\UserInterface
     *
     * @return void
     */
    public function eraseCredentials()
    {
    }

    /**
     * Returns the passwordHashSalt for this Account.
     *
     * This is required by \Symfony\Component\Security\Core\User\UserInterface
     *
     * @return string The passwordHashSalt for this Account.
     */
    public function getSalt()
    {
        if ($this->password === null) {
            return null;
        }
        return $this->password->getSalt();
    }

    /**
     * Returns the passwordHashSalt for this Account.
     *
     * @param UserInterface $user The user class.
     *
     * @return boolean True to tell the EquatableInterface we are a real user class.
     */
    public function isEqualTo(UserInterface $user)
    {
        if ($this->getUsername() === $user->getUsername()) {
            return true;
        }

        return false;
    }
}

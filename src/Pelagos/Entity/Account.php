<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

use JMS\Serializer\Annotation as Serializer;

use Pelagos\Exception\PasswordException;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\DataRepositoryRoles;
use Pelagos\Bundle\AppBundle\DataFixtures\ORM\ResearchGroupRoles;

/**
 * Entity class to represent an Account.
 *
 * This class defines an Account, which is a set of credentials for a Person.
 *
 * @ORM\Entity
 */
class Account extends Entity implements UserInterface, \Serializable
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
     * @ORM\Column
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
     * Constructor for Account.
     *
     * @param Person   $person   The Person this account is for.
     * @param string   $userId   The user ID for this account.
     * @param Password $password The password for this account.
     */
    public function __construct(Person $person = null, $userId = null, Password $password = null)
    {
        $this->passwordHistory = new ArrayCollection();
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
    public function setUserId($userId)
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
     * @param Password $password Pelagos password object.
     *
     * @throws PasswordException When password last changed within 24 hrs.
     * @throws PasswordException When an old password is re-used.
     *
     * @return void
     */
    public function setPassword(Password $password)
    {
        $this->password = $password;

        // check for minimum age.
        $interval = new \DateInterval('PT24H');
        $now = new \DateTime();
        if (!$this->passwordHistory->isEmpty() and
            $this->passwordHistory->first()->getModificationTimeStamp()->add($interval) > $now) {
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
            if ($personDataRepository->getRole()->getName() == DataRepositoryRoles::MANAGER
                and !in_array(self::ROLE_DATA_REPOSITORY_MANAGER, $roles)
            ) {
                $roles[] = self::ROLE_DATA_REPOSITORY_MANAGER;
            }
        }
        foreach ($this->getPerson()->getPersonResearchGroups() as $personResearchGroup) {
            if ($personResearchGroup->getRole()->getName() == ResearchGroupRoles::DATA
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
     * Serialize this Account.
     *
     * This is required by \Serializable.
     *
     * @return string Serialized Account string.
     */
    public function serialize()
    {
        return serialize(
            array(
            $this->person,
            $this->userId,
            )
        );
    }

    /**
     * Unserialize this Account.
     *
     * This is required by \Serializable.
     *
     * @param string $serialized Serialized Account string.
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        list (
            $this->person,
            $this->userId,
        ) = unserialize($serialized);
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
}

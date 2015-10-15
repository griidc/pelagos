<?php
/**
 * This file contains the implementation of the Person entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage Person
 */

namespace Pelagos\Entity;

use \Pelagos\Exception\EmptyRequiredArgumentException;
use \Pelagos\Exception\InvalidFormatArgumentException;
use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Class to represent people.
 */
class Person extends Entity
{

    /**
     * Person's first name.
     *
     * @var string $firstName
     *
     * @Assert\NotBlank(
     *     message="First name is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="First name cannot contain angle brackets (< or >)"
     * )
     */
    protected $firstName;

    /**
     * Person's last name.
     *
     * @var string $lastName
     *
     * @Assert\NotBlank(
     *     message="Last name is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Last name cannot contain angle brackets (< or >)"
     * )
     */
    protected $lastName;

    /**
     * Person's email address.
     *
     * @var string $emailAddress
     *
     * @Assert\NotBlank(
     *     message="Email address is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Email address cannot contain angle brackets (< or >)"
     * )
     * @Assert\Email(
     *     message="Email address is invalid"
     * )
     */
    protected $emailAddress;

    /**
     * Person's PersonFundingOrganizations.
     *
     * @var \Doctrine\Common\Collections\Collection $personFundingOrganizations
     *
     * @access protected
     */
    protected $personFundingOrganizations;

    /**
     * Person's PersonResearchGroups.
     *
     * @var \Doctrine\Common\Collections\Collection $personResearchGroups
     *
     * @access protected
     */
    protected $personResearchGroups;

    /**
     * Static array containing a list of the properties and their attributes.
     *
     * Used by common update code.
     *
     * @var array $properties
     */
    protected static $properties = array(
        'firstName' => array(
            'type' => 'string',
            'setter' => 'setFirstName',
            'getter' => 'getFirstName',
        ),
        'lastName' => array(
            'type' => 'string',
            'setter' => 'setLastName',
            'getter' => 'getLastName',
        ),
        'emailAddress' => array(
            'type' => 'string',
            'setter' => 'setEmailAddress',
            'getter' => 'getEmailAddress',
        ),
        'personResearchGroups' => array(
            'type' => 'object',
            'class' => '\Doctrine\Common\Collections\Collection',
            'getter' => 'getPersonResearchGroups',
            'setter' => 'setPersonResearchGroups',
            'serialize' => false,
        ),
    );

    /**
     * Setter for firstName property.
     *
     * @param string $firstName First name of the Person.
     *
     * @return void
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Getter for firstName property.
     *
     * @return string First name of the Person.
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Setter for lastName property.
     *
     * @param string $lastName Last name of the Person.
     *
     * @return void
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Getter for lastName property.
     *
     * @return string Last name of the Person.
     */
    public function getLastname()
    {
        return $this->lastName;
    }

    /**
     * Setter for emailAddress property.
     *
     * @param string $emailAddress Email address of the Person.
     *
     * @return void
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * Getter for emailAddress property.
     *
     * @return string Email address of the Person.
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Setter for personFundingOrganizations.
     *
     * @param array|\Traversable $personFundingOrganizations Set of PersonFundingOrganization objects.
     *
     * @access public
     *
     * @throws \Exception When $personFundingOrganizations is not an array or traversable object.
     * @throws \Exception When Non-PersonFundingOrganization found in $personFundingOrganizations.
     *
     * @return void
     */
    public function setPersonFundingOrganizations($personFundingOrganizations)
    {
        if (is_array($personFundingOrganizations) || $personFundingOrganizations instanceof \Traversable) {
            foreach ($personFundingOrganizations as $personFundingOrganization) {
                if (!$personFundingOrganization instanceof PersonFundingOrganization) {
                    throw new \Exception('Non-PersonFundingOrganization found in personFundingOrganizations.');
                }
            }
            $this->personFundingOrganizations = $personFundingOrganizations;
            foreach ($this->personFundingOrganizations as $personFundingOrganization) {
                $personFundingOrganization->setPerson($this);
            }
        } else {
            throw new \Exception('personFundingOrganizations must be either array or traversable objects.');
        }
    }

    /**
     * Setter for personResearchGroups.
     *
     * @param array|\Traversable $personResearchGroups Set of PersonResearchGroup objects.
     *
     * @access public
     *
     * @throws \Exception When Non-PersonResearchGroup found in $personResearchGroups.
     * @throws \Exception When $personResearchGroups is not an array or traversable object.
     *
     * @return void
     */
    public function setPersonResearchGroups($personResearchGroups)
    {
        if (is_array($personResearchGroups) || $personResearchGroups instanceof \Traversable) {
            foreach ($personResearchGroups as $personResearchGroup) {
                if (!$personResearchGroup instanceof PersonResearchGroup) {
                    throw new \Exception('Non-PersonResearchGroup found in personResearchGroups.');
                }
            }
            $this->personResearchGroups = $personResearchGroups;
            foreach ($this->personResearchGroups as $personResearchGroup) {
                $personResearchGroup->setPerson($this);
            }
        } else {
            throw new \Exception('personResearchGroups must be either array or traversable objects.');
        }
    }

    /**
     * Getter for personFundingOrganizations.
     *
     * @access public
     *
     * @return \Doctrine\Common\Collections\Collection Collection containing personFundingOrganizations
     *                                                 listings for this Person.
     */
    public function getPersonFundingOrganizations()
    {
        return $this->personFundingOrganizations;
    }

    /**
     * Getter for personResearchGroups.
     *
     * @access public
     *
     * @return \Doctrine\Common\Collections\Collection Collection containing personResearchGroups
     *                                                 listings for this research group.
     */
    public function getPersonResearchGroups()
    {
        return $this->personResearchGroups;
    }

    /**
     * Method that returns a Person's properties as an array.
     *
     * Default is to not localize time stamps.
     *
     * @param array   $properties         An array listing the properties to include.
     * @param boolean $localizeTimeStamps A flag to inidcate whether or not to localize time stamps.
     *
     * @return array An array of property values for this Person.
     */
    public function asArray(array $properties, $localizeTimeStamps = false)
    {
        $personArray = array();
        foreach ($properties as $property) {
            switch ($property) {
                case 'id':
                    $personArray[] = $this->getId();
                    break;
                case 'firstName':
                    $personArray[] = $this->getFirstName();
                    break;
                case 'lastName':
                    $personArray[] = $this->getLastName();
                    break;
                case 'emailAddress':
                    $personArray[] = $this->getEmailAddress();
                    break;
                case 'creationTimeStamp':
                    $personArray[] = $this->getCreationTimeStampAsISO($localizeTimeStamps);
                    break;
                case 'creator':
                    $personArray[] = $this->getCreator();
                    break;
                case 'modificationTimeStamp':
                    $personArray[] = $this->getModificationTimeStampAsISO($localizeTimeStamps);
                    break;
                case 'modifier':
                    $personArray[] = $this->getModifier();
                    break;
            }
        }
        return $personArray;
    }
}

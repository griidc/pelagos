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
use \Pelagos\Exception\NotDeletableException;
use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Class to represent people.
 */
class Person extends Entity
{
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
        'phoneNumber' => array(
            'type' => 'string',
            'setter' => 'setPhoneNumber',
            'getter' => 'getPhoneNumber',
        ),
        'deliveryPoint' => array(
            'type' => 'string',
            'setter' => 'setDeliveryPoint',
            'getter' => 'getDeliveryPoint',
        ),
        'city' => array(
            'type' => 'string',
            'setter' => 'setCity',
            'getter' => 'getCity',
        ),
        'administrativeArea' => array(
            'type' => 'string',
            'setter' => 'setAdministrativeArea',
            'getter' => 'getAdministrativeArea',
        ),
        'postalCode' => array(
            'type' => 'string',
            'setter' => 'setPostalCode',
            'getter' => 'getPostalCode',
        ),
        'country' => array(
            'type' => 'string',
            'setter' => 'setCountry',
            'getter' => 'getCountry',
        ),
        'url' => array(
            'type' => 'string',
            'setter' => 'setUrl',
            'getter' => 'getUrl',
        ),
        'organization' => array(
            'type' => 'string',
            'setter' => 'setOrganization',
            'getter' => 'getOrganization',
        ),
        'position' => array(
            'type' => 'string',
            'setter' => 'setPosition',
            'getter' => 'getPosition',
        ),
        'personFundingOrganizations' => array(
            'type' => 'object',
            'class' => '\Doctrine\Common\Collections\Collection',
            'getter' => 'getPersonFundingOrganizations',
            'setter' => 'setPersonFundingOrganizations',
            'serialize' => false,
        ),
        'personResearchGroups' => array(
            'type' => 'object',
            'class' => '\Doctrine\Common\Collections\Collection',
            'getter' => 'getPersonResearchGroups',
            'setter' => 'setPersonResearchGroups',
            'serialize' => false,
        ),
        'token' => array(
            'type' => 'object',
            'class' => '\Pelagos\Entity\Token',
            'getter' => 'getToken',
            'setter' => 'setToken',
            'serialize' => false,
        ),
    );

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
     * Person's telephone number.
     *
     * @var string $phoneNumber
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Phone number cannot contain angle brackets (< or >)"
     * )
     */
    protected $phoneNumber;

    /**
     * Person's delivery point (street address).
     *
     * @var string
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Delievery point (address) cannot contain angle brackets (< or >)"
     * )
     */
    protected $deliveryPoint;

    /**
     * Person's city.
     *
     * @var string
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="City cannot contain angle brackets (< or >)"
     * )
     */
    protected $city;

    /**
     * Person's administrative area (state).
     *
     * @var string
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Administrative area (state) cannot contain angle brackets (< or >)"
     * )
     */
    protected $administrativeArea;

    /**
     * Person's postal code (zipcode).
     *
     * @var string
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Postal code (zip) cannot contain angle brackets (< or >)"
     * )
     */
    protected $postalCode;

    /**
     * Person's country.
     *
     * @var string
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Country cannot contain angle brackets (< or >)"
     * )
     */
    protected $country;

    /**
     * Person's Website url.
     *
     * @var string
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Website URL cannot contain angle brackets (< or >)"
     * )
     */
    protected $url;

    /**
     * Person's organization.
     *
     * @var string
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Organization cannot contain angle brackets (< or >)"
     * )
     */
    protected $organization;

    /**
     * Person's position.
     *
     * @var string
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Position cannot contain angle brackets (< or >)"
     * )
     */
    protected $position;

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
     * Person's Token.
     *
     * @var \Pelagos\Entity\Token $token
     *
     * @access protected
     */
    protected $token;

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
     * Setter for phoneNumber.
     *
     * @param string $phoneNumber Person's phone number.
     *
     * @access public
     *
     * @return void
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Getter for phoneNumber.
     *
     * @access public
     *
     * @return string Phone number of Person.
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for deliveryPoint.
     *
     * @param string $deliveryPoint Street address of Person.
     *
     * @access public
     *
     * @return void
     */
    public function setDeliveryPoint($deliveryPoint)
    {
        $this->deliveryPoint = $deliveryPoint;
    }

    /**
     * Getter for deliveryPoint.
     *
     * @access public
     *
     * @return string Street address of Person.
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Setter for city.
     *
     * @param string $city City of Person.
     *
     * @access public
     *
     * @return void
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Getter for city.
     *
     * @access public
     *
     * @return string City of Person.
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for administrativeArea.
     *
     * @param string $administrativeArea Person's administrative area (state).
     *
     * @access public
     *
     * @return void
     */
    public function setAdministrativeArea($administrativeArea)
    {
        $this->administrativeArea = $administrativeArea;
    }

    /**
     * Getter for administrativeArea.
     *
     * @access public
     *
     * @return string Person's administrative area (state).
     */
    public function getAdministrativeArea()
    {
        return $this->administrativeArea;
    }

    /**
     * Setter for postalCode.
     *
     * @param string $postalCode Postal (zip) code.
     *
     * @access public
     *
     * @return void
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Getter for postalCode.
     *
     * @access public
     *
     * @return string Containing postal (zip) code.
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Setter for country.
     *
     * @param string $country Person's country.
     *
     * @access public
     *
     * @return void
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * Getter for country.
     *
     * @access public
     *
     * @return string Person's country.
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter for url.
     *
     * @param string $url Person's Website URL.
     *
     * @access public
     *
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Getter for url.
     *
     * @access public
     *
     * @return string URL of Person's Website.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for organization.
     *
     * @param string $organization Person's organization.
     *
     * @access public
     *
     * @return void
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * Getter for organization.
     *
     * @access public
     *
     * @return string Person's organization.
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Setter for position.
     *
     * @param string $position Person's position.
     *
     * @access public
     *
     * @return void
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Getter for position.
     *
     * @access public
     *
     * @return string Person's position.
     */
    public function getPosition()
    {
        return $this->position;
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
     * Setter for token.
     *
     * @param Token $token Person's token.
     *
     * @access public
     *
     * @return void
     */
    public function setToken(Token $token = null)
    {
        $this->token = $token;
        if ($this->token !== null) {
            $this->token->setPerson($this);
        }
    }

    /**
     * Getter for token.
     *
     * @access public
     *
     * @return Token Person's token.
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Check if this Person is deletable.
     *
     * This method throws a NotDeletableException when the Person has associated FundingOrganizations or
     * ResearchGroups. The NotDeletableException will have its reasons set to a list of reasons the Person
     * is not deletable.
     *
     * @throws NotDeletableException When the Person has associated FundingOrganizations or ResearchGroups.
     *
     * @return void
     */
    public function checkDeletable()
    {
        $notDeletableReasons = array();
        $personFundingOrganizationCount = count($this->getPersonFundingOrganizations());
        if ($personFundingOrganizationCount > 0) {
            $notDeletableReasons[] = 'there ' . ($personFundingOrganizationCount > 1 ? 'are' : 'is') .
                " $personFundingOrganizationCount associated Funding Organization" .
                ($personFundingOrganizationCount > 1 ? 's' : '');
        }
        $personResearchGroupCount = count($this->getPersonResearchGroups());
        if ($personResearchGroupCount > 0) {
            $notDeletableReasons[] = 'there ' . ($personResearchGroupCount > 1 ? 'are' : 'is') .
                " $personResearchGroupCount associated Research Group" .
                ($personResearchGroupCount > 1 ? 's' : '');
        }
        if (count($notDeletableReasons) > 0) {
            $notDeletableException = new NotDeletableException();
            $notDeletableException->setReasons($notDeletableReasons);
            throw $notDeletableException;
        }
    }
}

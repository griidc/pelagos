<?php
/**
 * This file contains the implementation of the FundingOrganization entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage FundingOrganization
 */

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Class to represent funding organizations.
 */
class FundingOrganization extends Entity
{
    /**
     * Name of a funding organization.
     *
     * @var string $name
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Name is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Name cannot contain angle brackets (< or >)"
     * )
     */
    protected $name;

    /**
     * Funding organization's logo.
     *
     * @var string $logo
     * @access protected
     */
    protected $logo;

    /**
     * Funding organization's email address.
     *
     * @var string $emailAddress
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Email address cannot contain angle brackets (< or >)"
     * )
     * @Assert\Email(
     *     message="Email address is invalid"
     * )
     */
    protected $emailAddress;

    /**
     * Description of a funding organization.
     *
     * @var string $description
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Description cannot contain angle brackets (< or >)"
     * )
     */
    protected $description;

    /**
     * Funding organization's Website url.
     *
     * @var string $url
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Website URL cannot contain angle brackets (< or >)"
     * )
     */
    protected $url;

    /**
     * Funding organization's telephone number.
     *
     * @var string $phoneNumber
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Phone number cannot contain angle brackets (< or >)"
     * )
     */
    protected $phoneNumber;

    /**
     * Funding organization's delivery point (street address).
     *
     * @var string $deliveryPoint
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Delievery point (address) cannot contain angle brackets (< or >)"
     * )
     */
    protected $deliveryPoint;

    /**
     * Funding organization's city.
     *
     * @var string $city
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="City cannot contain angle brackets (< or >)"
     * )
     */
    protected $city;

    /**
     * Funding organization's administrative area (state).
     *
     * @var string $administrativeArea
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Administrative area (state) cannot contain angle brackets (< or >)"
     * )
     */
    protected $administrativeArea;

    /**
     * Funding organization's postal code (zipcode).
     *
     * @var string $postalCode
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Postal code (zip) cannot contain angle brackets (< or >)"
     * )
     */
    protected $postalCode;

    /**
     * Funding organization's country.
     *
     * @var string $country
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Country cannot contain angle brackets (< or >)"
     * )
     */
    protected $country;

    /**
     * Setter for name.
     *
     * @param string $name Textual name of funding organization.
     *
     * @access public
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Getter for name.
     *
     * @access public
     *
     * @return string String containing name of funding organization.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for logo.
     *
     * @param string $logo Containing byte string of logo.
     *
     * @access public
     *
     * @return void
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * Getter for logo.
     *
     * @access public
     *
     * @return string Containing logo encoded as byte text.
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Setter for emailAddress.
     *
     * @param string $emailAddress Containing email address of funding organization.
     *
     * @access public
     *
     * @return void
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * Getter for emailAddress.
     *
     * @access public
     *
     * @return string Containing emailADdress.
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Setter for description.
     *
     * @param string $description Description of funding organization.
     *
     * @access public
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Getter for description.
     *
     * @access public
     *
     * @return string Description of funding organization.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setter for url.
     *
     * @param string $url Funding organization's Website URL.
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
     * @return string URL of funding organization's Website.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for phoneNumber.
     *
     * @param string $phoneNumber Funding organization's phone number.
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
     * @return string Phone number of funding organization.
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for deliveryPoint.
     *
     * @param string $deliveryPoint Street address of funding organization.
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
     * @return string Street address of funding organization.
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Setter for city.
     *
     * @param string $city City of funding organization.
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
     * @return string City of funding organization.
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for administrativeArea.
     *
     * @param string $administrativeArea Funding organization's administrative area (state).
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
     * @return string Funding organization's administrative area (state).
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
     * @param string $country Funding organization's country.
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
     * @return string Funding organization's country.
     */
    public function getCountry()
    {
        return $this->country;
    }



    /**
     * Method to update multiple properties.
     *
     * @param array $updates An associative array indexed with property names
     *                       and containing each property's new value.
     *
     * @return FundingOrganization Return the updated object.
     */
    public function update(array $updates)
    {
        foreach ($updates as $field => $value) {
            switch($field) {
                case 'name':
                    $this->setName($value);
                    break;
                case 'logo':
                    $this->setLogo($value);
                    break;
                case 'emailAddress':
                    $this->setEmailAddress($value);
                    break;
                case 'description':
                    $this->setDescription($value);
                    break;
                case 'url':
                    $this->setUrl($value);
                    break;
                case 'phoneNumber':
                    $this->setPhoneNumber($value);
                    break;
                case 'deliveryPoint':
                    $this->setDeliveryPoint($value);
                    break;
                case 'city':
                    $this->setCity($value);
                    break;
                case 'administrativeArea':
                    $this->setAdministrativeArea($value);
                    break;
                case 'postalCode':
                    $this->setPostalCode($value);
                    break;
                case 'country':
                    $this->setCountry($value);
                    break;
                case 'creator':
                    $this->setCreator($value);
                    break;
            }
        }
        return $this;
    }

    /**
     * Override jsonSerialize.
     *
     * @return array An array suitable for JSON serialization of the object.
     */
    public function jsonSerialize()
    {
        return array(
            'id' => $this->getId(),
            'creationTimeStamp' => $this->getCreationTimeStampAsISO(),
            'creator' => $this->getCreator(),
            'name' => $this->getName(),
            'emailAddress' => $this->getEmailAddress(),
            'description' => $this->getDescription(),
            'url' => $this->getUrl(),
            'phoneNumber' => $this->getPhoneNumber(),
            'deliveryPoint' => $this->getDeliveryPoint(),
            'city' => $this->getCity(),
            'administrativeArea' => $this->getAdministrativeArea(),
            'postalCode' => $this->getPostalCode(),
            'country' => $this->getCountry(),
            'modificationTimeStamp' => $this->getModificationTimeStampAsISO(),
            'modifier' => $this->getModifier()
        );
    }

    /**
     * Method that returns a FundingOrganization's properties as an array.
     *
     * Default is to not localize time stamps.
     *
     * @param array   $properties         An array listing the properties to include.
     * @param boolean $localizeTimeStamps A flag to inidcate whether or not to localize time stamps.
     *
     * @return array An array of property values for this FundingOrganization.
     */
    public function asArray(array $properties, $localizeTimeStamps = false)
    {
        $personArray = array();
        foreach ($properties as $property) {
            switch($property) {
                case 'id':
                    $personArray[] = $this->getId();
                    break;
                case 'name':
                    $personArray[] = $this->getName();
                    break;
                case 'description':
                    $personArray[] = $this->getDescription();
                    break;
                case 'phoneNumber':
                    $personArray[] = $this->getPhoneNumber();
                    break;
                case 'emailAddress':
                    $personArray[] = $this->getEmailAddress();
                    break;
                case 'url':
                    $personArray[] = $this->getUrl();
                    break;
                case 'administrativeArea':
                    $personArray[] = $this->getAdministrativeArea();
                    break;
                case 'postalCode':
                    $personArray[] = $this->getPostalCode();
                    break;
                case 'country':
                    $personArray[] = $this->getCountry();
                    break;
                case 'logo':
                    $personArray[] = $this->getLogo();
                    break;
                case 'creationTimeStamp':
                    $personArray[] = $this->getCreationTimeStamp($localizeTimeStamps);
                    break;
                case 'creator':
                    $personArray[] = $this->getCreator();
                    break;
                case 'modificationTimeStamp':
                    $personArray[] = $this->getModificationTimeStamp($localizeTimeStamps);
                    break;
                case 'modifier':
                    $personArray[] = $this->getModifier();
                    break;
            }
        }
        return $personArray;
    }
}

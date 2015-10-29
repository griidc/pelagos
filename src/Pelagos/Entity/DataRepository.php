<?php
/**
 * This file contains the implementation of the DataRepository entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage DataRepository
 */

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;
use \Pelagos\Exception\NotDeletableException;

/**
 * Class to represent a Data Repository.
 *
 * @Assert\UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="A Funding Organization with this name already exists"
 * )
 */
class DataRepository extends Entity
{
    /**
     * Name of a Data Repository.
     *
     * @var string $name
     *
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
     * Data Repository's email address.
     *
     * @var string $emailAddress
     *
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
     * Description of a Data Repository.
     *
     * @var string $description
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Description cannot contain angle brackets (< or >)"
     * )
     */
    protected $description;

    /**
     * Data Repository's Website url.
     *
     * @var string $url
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Website URL cannot contain angle brackets (< or >)"
     * )
     */
    protected $url;

    /**
     * Data Repository's telephone number.
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
     * Data Repository's delivery point (street address).
     *
     * @var string $deliveryPoint
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Delievery point (address) cannot contain angle brackets (< or >)"
     * )
     */
    protected $deliveryPoint;

    /**
     * Data Repository's city.
     *
     * @var string $city
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="City cannot contain angle brackets (< or >)"
     * )
     */
    protected $city;

    /**
     * Data Repository's administrative area (state).
     *
     * @var string $administrativeArea
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Administrative area (state) cannot contain angle brackets (< or >)"
     * )
     */
    protected $administrativeArea;

    /**
     * Data Repository's postal code (zipcode).
     *
     * @var string $postalCode
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Postal code (zip) cannot contain angle brackets (< or >)"
     * )
     */
    protected $postalCode;

    /**
     * Data Repository's country.
     *
     * @var string $country
     *
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="Country cannot contain angle brackets (< or >)"
     * )
     */
    protected $country;



    /**
     * Funding Organization's relationship with Persons.
     *
     * @var \Doctrine\Common\Collections\Collection $personDataRepositories
     *
     * @access protected
     */
    protected $personDataRepositories;

    /**
     * Setter for name.
     *
     * @param string $name name of Data Repository.
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
     * @return string String containing name of Data Repository.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for emailAddress.
     *
     * @param string $emailAddress Containing email address of Data Repository.
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
     * @param string $description Description of Data Repository.
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
     * @return string Description of Data Repository.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setter for url.
     *
     * @param string $url Data Repository's Website URL.
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
     * @return string URL of Data Repository's Website.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for phoneNumber.
     *
     * @param string $phoneNumber Data Repository's phone number.
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
     * @return string Phone number of Data Repository.
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for deliveryPoint.
     *
     * @param string $deliveryPoint Street address of Data Repository.
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
     * @return string Street address of Data Repository.
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Setter for city.
     *
     * @param string $city City of Data Repository.
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
     * @return string City of Data Repository.
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for administrativeArea.
     *
     * @param string $administrativeArea Data Repository's administrative area (state).
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
     * @return string Data Repository's administrative area (state).
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
     * @param string $country Data Repository's country.
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
     * @return string Data Repository's country.
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter for personDataRepositories.
     *
     * @param array|\Traversable $personDataRepositories Set of PersonDataRepository objects.
     *
     * @access public
     *
     * @throws \Exception When $personDataRepositories is not an array or traversable object.
     * @throws \Exception When Non-PersonDataRepository found within $personDataRepositories.
     *
     * @return void
     */
    public function setPersonDataRepositories($personDataRepositories)
    {
        if (is_array($personDataRepositories) || $personDataRepositories instanceof \Traversable) {
            foreach ($personDataRepositories as $personDataRepository) {
                if (!$personDataRepository instanceof PersonDataRepository) {
                    throw new \Exception('Non-PersonDataRepository found in personDataRepositories.');
                }
            }
            $this->personDataRepositories = $personDataRepositories;
            foreach ($this->personDataRepositories as $personDataRepository) {
                $personDataRepository->setDataRepository($this);
            }
        } else {
            throw new \Exception('personDataRepositories must be either array or traversable objects.');
        }
    }

    /**
     * Getter for personDataRepositories.
     *
     * @access public
     *
     * @return \Doctrine\Common\Collections\Collection Collection containing personDataRepositories
     *                                                 listings for this Funding Organization.
     */
    public function getPersonDataRepositories()
    {
        return $this->personDataRepositories;
    }

    /**
     * Static array containing a list of the properties and their attributes.
     *
     * @var array $properties
     */
    protected static $properties = array(
        'name' => array(
            'type' => 'string',
            'getter' => 'getName',
            'setter' => 'setName',
        ),
        'emailAddress' => array(
            'type' => 'string',
            'getter' => 'getEmailAddress',
            'setter' => 'setEmailAddress',
        ),
        'description' => array(
            'type' => 'string',
            'getter' => 'getDescription',
            'setter' => 'setDescription',
        ),
        'url' => array(
            'type' => 'string',
            'getter' => 'getUrl',
            'setter' => 'setUrl',
        ),
        'phoneNumber' => array(
            'type' => 'string',
            'getter' => 'getPhoneNumber',
            'setter' => 'setPhoneNumber',
        ),
        'deliveryPoint' => array(
            'type' => 'string',
            'getter' => 'getDeliveryPoint',
            'setter' => 'setDeliveryPoint',
        ),
        'city' => array(
            'type' => 'string',
            'getter' => 'getCity',
            'setter' => 'setCity',
        ),
        'administrativeArea' => array(
            'type' => 'string',
            'getter' => 'getAdministrativeArea',
            'setter' => 'setAdministrativeArea',
        ),
        'postalCode' => array(
            'type' => 'string',
            'getter' => 'getPostalCode',
            'setter' => 'setPostalCode',
        ),
        'country' => array(
            'type' => 'string',
            'getter' => 'getCountry',
            'setter' => 'setCountry',
        ),
        'personDataRepositories' => array(
            'type' => 'object',
            'class' => '\Doctrine\Common\Collections\Collection',
            'getter' => 'getPersonDataRepositories',
            'setter' => 'setPersonDataRepositories',
            'serialize' => false,
        ),
    );

    /**
     * Method that returns a DataRepository's properties as an array.
     *
     * Default is to not localize time stamps.
     *
     * @param array   $properties         An array listing the properties to include.
     * @param boolean $localizeTimeStamps A flag to inidcate whether or not to localize time stamps.
     *
     * @return array An array of property values for this DataRepository.
     */
    public function asArray(array $properties, $localizeTimeStamps = false)
    {
        $personArray = array();
        foreach ($properties as $property) {
            switch ($property) {
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
                case 'city':
                    $personArray[] = $this->getCity();
                    break;
                case 'deliveryPoint':
                    $personArray[] = $this->getDeliveryPoint();
                    break;
            }
        }
        return $personArray;
    }

    /**
     * Check if this DataRepository is deletable.
     *
     * This method throws a NotDeletableException when the DataRepository has associated
     * Persons. The NotDeletableException will have its reasons set to a list of
     * reasons the DataRepository is not deletable.
     *
     * @throws NotDeletableException When the DataRepository has associated Persons.
     *
     * @return void
     */
    public function checkDeletable()
    {
        $notDeletableReasons = array();

        $personDataRepositoryCount = count($this->getPersonDataRepositories());
        if ($personDataRepositoryCount > 0) {
            $notDeletableReasons[] = 'there ' . ($personDataRepositoryCount > 1 ? 'are' : 'is') .
                " $personDataRepositoryCount associated Person" .
                ($personDataRepositoryCount > 1 ? 's' : '');
        }
        if (count($notDeletableReasons) > 0) {
            $notDeletableException = new NotDeletableException();
            $notDeletableException->setReasons($notDeletableReasons);
            throw $notDeletableException;
        }
    }
}

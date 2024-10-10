<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

/**
 * This class represent data center entity information.
 *
 * @UniqueEntity(
 *     fields={"organizationName"},
 *     errorPath="organizationName",
 *     message="A data center with this name already exists"
 * )
 *
 * @UniqueEntity(
 *     fields={"organizationUrl"},
 *     errorPath="organizationUrl",
 *     message="A data center with this Url already exists"
 * )
 */
/**
 * DataCenter to National Data Center association abstract class.
 */
#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['datacenter' => 'DataCenter', 'nationaldatacenter' => 'NationalDataCenter'])]
class DataCenter extends Entity
{
    /**
     * Name of the Data Center.
     *
     * @var string
     *
     */
    #[ORM\Column(type: 'citext', unique: true)]
    #[Assert\NotBlank(message: 'Organization name is required')]
    protected $organizationName;

    /**
     * Data center's URL.
     *
     * @var string
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Website URL cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', unique: true)]
    #[Assert\NotBlank(message: 'Organization URL is required')]
    protected $organizationUrl;

    /**
     * Data center's phone number.
     *
     * @var string
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Phone number cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $phoneNumber;

    /**
     * Data center's delivery point.
     *
     * @var string
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Delievery point (address) cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $deliveryPoint;

    /**
     * Data center's city.
     *
     * @var string
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'City cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $city;

    /**
     * Data center's administrative area (state).
     *
     * @var string
     *
     * @access protected
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Administrative area (state) cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $administrativeArea;

    /**
     * Data center's postal code.
     *
     * @var string
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Postal code (zip) cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $postalCode;

    /**
     * Data center's country.
     *
     * @var string
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Country cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    protected $country;

    /**
     * Data center's email address.
     *
     * @var string
     *
     *
     */
    #[Assert\Regex(pattern: '/[<>]/', match: false, message: 'Phone number cannot contain angle brackets (< or >)')]
    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Email(message: 'Email address is invalid', mode: 'strict')]
    protected $emailAddress;

    /**
     * Getter for Data center organization's name.
     *
     * @return string
     */
    public function getOrganizationName()
    {
        return $this->organizationName;
    }

    /**
     * Setter for Data center organization's name.
     *
     * @param string $organizationName The data center's name.
     *
     * @return void
     */
    public function setOrganizationName(string $organizationName)
    {
        $this->organizationName = $organizationName;
    }

    /**
     * Getter for Data center organization's URL.
     *
     * @return string
     */
    public function getOrganizationUrl()
    {
        return $this->organizationUrl;
    }

    /**
     * Setter for Data center organization's URL.
     *
     * @param string $organizationUrl The data center's URL.
     *
     * @return void
     */
    public function setOrganizationUrl(string $organizationUrl)
    {
        $this->organizationUrl = $organizationUrl;
    }

    /**
     * Getter for Data center's phone number.
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for Data center's phone number.
     *
     * @param string|null $phoneNumber Phone number of the organization.
     *
     * @return void
     */
    public function setPhoneNumber(?string $phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Getter for Data center delivery point(street address).
     *
     * @return string
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Setter for Data center delivery point(street address).
     *
     * @param string|null $deliveryPoint Delivery point(street address) of the organization.
     *
     * @return void
     */
    public function setDeliveryPoint(?string $deliveryPoint)
    {
        $this->deliveryPoint = $deliveryPoint;
    }

    /**
     * Getter for Data center's city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for Data center's city.
     *
     * @param string|null $city City where the organization is located.
     *
     * @return void
     */
    public function setCity(?string $city)
    {
        $this->city = $city;
    }

    /**
     * Getter for Data center's administrative area(state).
     *
     * @return string
     */
    public function getAdministrativeArea()
    {
        return $this->administrativeArea;
    }

    /**
     * Setter for Data center's administrative area(state).
     *
     * @param string|null $administrativeArea Administrative area(state) where the organization is located.
     *
     * @return void
     */
    public function setAdministrativeArea(?string $administrativeArea)
    {
        $this->administrativeArea = $administrativeArea;
    }

    /**
     * Getter for Data center's postal code.
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Setter for Data center's postal code.
     *
     * @param string|null $postalCode Postal code for the organization.
     *
     * @return void
     */
    public function setPostalCode(?string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Getter for Data center's country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter for Data center's country.
     *
     * @param string|null $country Country of the organization.
     *
     * @return void
     */
    public function setCountry(?string $country)
    {
        $this->country = $country;
    }

    /**
     * Getter for Email address of the organization.
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Setter for Email address of the organization.
     *
     * @param string|null $emailAddress Email address of the organization.
     *
     * @return void
     */
    public function setEmailAddress(?string $emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }
}

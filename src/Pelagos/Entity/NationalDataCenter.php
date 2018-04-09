<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Hateoas\Configuration\Annotation as Hateoas;

/**
 * This class represent National data center entity information.
 *
 * @ORM\Entity
 *
 * @UniqueEntity(
 *     fields={"organizationName"},
 *     errorPath="organizationName",
 *     message="A National data center with this name already exists"
 * )
 *
 * @UniqueEntity(
 *     fields={"organizationUrl"},
 *     errorPath="organizationUrl",
 *     message="A National data center with this Url already exists"
 * )
 *
 * @Hateoas\Relation(
 *   "self",
 *   href = @Hateoas\Route(
 *     "pelagos_api_national_data_center_get",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "edit",
 *   href = @Hateoas\Route(
 *     "pelagos_api_national_data_center_put",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not service('security.authorization_checker').isGranted(['CAN_EDIT'], object))"
 *   )
 * )
 * @Hateoas\Relation(
 *   "delete",
 *   href = @Hateoas\Route(
 *     "pelagos_api_national_data_center_delete",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not object.isDeletable() or not service('security.authorization_checker').isGranted(['CAN_DELETE'], object))"
 *   )
 * )
 */
class NationalDataCenter extends Entity
{
    /**
     * Name of the National Data Center.
     *
     * @var string
     *
     * @ORM\Column(type="citext", unique=true)
     *
     * @Assert\NotBlank(
     *     message="Organization name is required"
     * )
     */
    protected $organizationName;

    /**
     * National Data center's URL.
     *
     * @var string
     *
     * @ORM\Column(type="text", unique=true)
     *
     * @Assert\NotBlank(
     *     message="Organization URL is required"
     * )
     *
     * @Assert\NoAngleBrackets(
     *     message="Website URL cannot contain angle brackets (< or >)"
     * )
     */
    protected $organizationUrl;

    /**
     * National Data center's phone number.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NoAngleBrackets(
     *     message="Phone number cannot contain angle brackets (< or >)"
     * )
     */
    protected $phoneNumber;

    /**
     * National Data center's delivery point.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NoAngleBrackets(
     *     message="Delievery point (address) cannot contain angle brackets (< or >)"
     * )
     */
    protected $deliveryPoint;

    /**
     * National Data center's city.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NoAngleBrackets(
     *     message="City cannot contain angle brackets (< or >)"
     * )
     */
    protected $city;

    /**
     * National Data center's administrative area (state).
     *
     * @var string
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NoAngleBrackets(
     *     message="Administrative area (state) cannot contain angle brackets (< or >)"
     * )
     */
    protected $administrativeArea;

    /**
     * National Data center's postal code.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NoAngleBrackets(
     *     message="Postal code (zip) cannot contain angle brackets (< or >)"
     * )
     */
    protected $postalCode;

    /**
     * National Data center's country.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NoAngleBrackets(
     *     message="Country cannot contain angle brackets (< or >)"
     * )
     */
    protected $country;

    /**
     * National Data center's email address.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NoAngleBrackets(
     *     message="Phone number cannot contain angle brackets (< or >)"
     * )
     *
     * @Assert\Email(
     *     message="Email address is invalid",
     *     strict=true
     * )
     */
    protected $emailAddress;

    /**
     * Distribution Point associated with the submission.
     *
     * @var \Doctrine\Common\Collections\Collection $distributionPoints
     *
     * @access protected
     *
     * @ORM\OneToMany(targetEntity="DistributionPoint", mappedBy="nationalDataCenter")
     *
     * @ORM\OrderBy({"creationTimeStamp" = "ASC"})
     */
    protected $distributionPoints;

    /**
     * Getter for National Data center organization's name.
     *
     * @return string
     */
    public function getOrganizationName()
    {
        return $this->organizationName;
    }

    /**
     * Setter for National Data center organization's name.
     *
     * @param string $organizationName The national data center's name.
     *
     * @return void
     */
    public function setOrganizationName($organizationName)
    {
        $this->organizationName = $organizationName;
    }

    /**
     * Getter for National Data center organization's URL.
     *
     * @return string
     */
    public function getOrganizationUrl()
    {
        return $this->organizationUrl;
    }

    /**
     * Setter for National Data center organization's URL.
     *
     * @param string $organizationUrl The national data center's URL.
     *
     * @return void
     */
    public function setOrganizationUrl($organizationUrl)
    {
        $this->organizationUrl = $organizationUrl;
    }

    /**
     * Getter for National Data center's phone number.
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for National Data center's phone number.
     *
     * @param string $phoneNumber Phone number of the organization.
     *
     * @return void
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Getter for National Data center delivery point(street address).
     *
     * @return string
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Setter for National Data center delivery point(street address).
     *
     * @param string $deliveryPoint Delivery point(street address) of the organization.
     *
     * @return void
     */
    public function setDeliveryPoint($deliveryPoint)
    {
        $this->deliveryPoint = $deliveryPoint;
    }

    /**
     * Getter for National Data center's city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for National Data center's city.
     *
     * @param string $city City where the organization is located.
     *
     * @return void
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * Getter for National Data center's administrative area(state).
     *
     * @return string
     */
    public function getAdministrativeArea()
    {
        return $this->administrativeArea;
    }

    /**
     * Setter for National Data center's administrative area(state).
     *
     * @param string $administrativeArea Administrative area(state) where the organization is located.
     *
     * @return void
     */
    public function setAdministrativeArea($administrativeArea)
    {
        $this->administrativeArea = $administrativeArea;
    }

    /**
     * Getter for National Data center's postal code.
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Setter for National Data center's postal code.
     *
     * @param string $postalCode Postal code for the organization.
     *
     * @return void
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Getter for National Data center's country.
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter for National Data center's country.
     *
     * @param string $country Country of the organization.
     *
     * @return void
     */
    public function setCountry($country)
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
     * @param string $emailAddress Email address of the organization.
     *
     * @return void
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * Setter for distributionPoints.
     *
     * @param array|\Traversable $distributionPoints Set of Distribution points objects.
     *
     * @access public
     *
     * @throws \Exception When Non-DistributionPoint found in DistributionPoints.
     * @throws \Exception When $distributionPoints is not an array or traversable object.
     *
     * @return void
     */
    public function setDistributionPoints($distributionPoints)
    {
        if (is_array($distributionPoints) || $distributionPoints instanceof \Traversable) {
            $this->distributionPoints = $distributionPoints;
            foreach ($distributionPoints as $distributionPoint) {
                if (!$distributionPoint instanceof DistributionPoint) {
                    throw new \Exception('Non-DistributionPoint found in distributionPoints.');
                }
                $distributionPoint->setNationalDataCenter($this);
            }
        } else {
            throw new \Exception('distributionPoints must be either array or traversable objects.');
        }
    }

    /**
     * Getter for distributionPoints.
     *
     * @access public
     *
     * @return \Doctrine\Common\Collections\Collection Collection containing distributionPoints
     *                                                 listings for this distribution contact (National Data Center).
     */
    public function getDistributionPoints()
    {
        return $this->distributionPoints;
    }
}

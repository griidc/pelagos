<?php

namespace Pelagos\Entity;

/**
 * This file contains the implementation of the FundingOrganization entity class.
 */
class FundingOrganization
{
    // --- ATTRIBUTES ---

    /**
     * Identifier for a funding organization.
     *
     * @var $id integer
     * @access protected
     */
    protected $id;

    /**
     * Name of a funding organization.
     *
     * @var string $name
     * @access protected
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
     */
    protected $emailAddress;

    /**
     * Description of a funding organization.
     *
     * @var string $description
     * @access protected
     */
    protected $description;

    /**
     * Funding organization's Website url.
     *
     * @var string $url
     * @access protected
     */
    protected $url;

    /**
     * Funding organization's telephone number.
     *
     * @var string $phoneNumber
     * @access protected
     */
    protected $phoneNumber;

    /**
     * Funding organization's delivery point (street address).
     *
     * @var string $deliveryPoint
     * @access protected
     */
    protected $deliveryPoint;

    /**
     * Funding organization's city.
     *
     * @var string $city
     * @access protected
     */
    protected $city;

    /**
     * Funding organization's administrative area (state).
     *
     * @var string $administrativeArea
     * @access protected
     */
    protected $administrativeArea;

    /**
     * Funding organization's postal code (zipcode).
     *
     * @var string $postalCode
     * @access protected
     */
    protected $postalCode;

    /**
     * Funding organization's country.
     *
     * @var string $country
     * @access protected
     */
    protected $country;

    // --- OPERATIONS ---

    /**
     * Getter for id.
     *
     * @access public
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

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
     * Settter for description.
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
     * Short description of method getCountry.
     *
     * @access public
     *
     * @return string Funding organization's country.
     */
    public function getCountry()
    {
        return $this->country;
    }
}

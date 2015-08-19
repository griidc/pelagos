<?php
/**
 * This file contains the implementation of the FundingCycle entity class.
 *
 * @package    Pelagos\Entity
 * @subpackage FundingCycle
 */

namespace Pelagos\Entity;

use \Symfony\Component\Validator\Constraints as Assert;

/**
 * Class to represent funding cycles.
 */
class FundingCycle extends Entity
{
    /**
     * Name of a funding cycle.
     *
     * @var string $name
     * @access protected
     *
     * @Assert\NotBlank(
     *     message="Name is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Last name cannot contain angle brackets (< or >)"
     * )
     */
    protected $name;

    /**
     * Description of a funding cycle.
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
     * Funding cycle's Website url.
     *
     * @var string $url
     * @access protected
     *
     * @Assert\NoAngleBrackets(
     *     message="URL cannot contain angle brackets (< or >)"
     * )
     */
    protected $url;

    /**
     * Funding cycle's start date.
     *
     * @var \Datetime $startDate
     * @access protected
     *
     */
    protected $startDate;

    /**
     * Funding cycle's end date.
     *
     * @var \Datetime $endDate
     * @access protected
     *
     */
    protected $endDate;

    /**
     * Funding cycle's Funding Organization.
     *
     * @var FundingOrganization
     * @access protected
     *
     */
    protected $fundingOrganization;

    /**
     * Setter for name.
     *
     * @param string $name Textual name of funding cycle.
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
     * @return string String containing name of funding cycle.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for description.
     *
     * @param string $description Description of funding cycle.
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
     * @return string Description of funding cycle.
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Setter for Funding Cycle.
     *
     * @param FundingOrganization $fundingOrg the funding organization.
     *
     * @access public
     *
     * @return void
     */
    public function setFundingOrganization(FundingOrganization $fundingOrg)
    {
        $this->fundingOrganization = $fundingOrg;
    }

    /**
     * Getter for Funding Organization.
     *
     * @access public
     *
     * @return FundingOrganization Funding Organization.
     */
    public function getFundingOrganization()
    {
        return $this->fundingOrganization;
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
     * @return string URL of funding cycle's Website.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for startDate.
     *
     * @access public
     *
     * @return void
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * Getter for startDate.
        *
     * @access public
        *
     * @return \Datetime startDate of funding cycle's Website.
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Setter for endDate.
        *
     * @access public
        *
     * @return void
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * Getter for endDate.
        *
     * @access public
        *
     * @return \Datetime endDate of funding cycle's Website.
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Method to update multiple properties.
     *
     * @param array $updates An associative array indexed with property names
     *                       and containing each property's new value.
     *
     * @return FundingCycle Return the updated object.
     */
    public function update(array $updates)
    {
        echo "\n\nEntity::update()";
        foreach ($updates as $field => $value) {
            switch($field) {
                case 'name':
                    $this->setName($value);
                    break;
                case 'description':
                    $this->setDescription($value);
                    break;
                case 'url':
                    $this->setUrl($value);
                    break;
                case 'creator':
                    $this->setCreator($value);
                    echo "\n\nEntity::update(".$field.")";
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
            'description' => $this->getDescription(),
            'url' => $this->getUrl(),
            'modificationTimeStamp' => $this->getModificationTimeStampAsISO(),
            'modifier' => $this->getModifier()
        );
    }

    /**
     * Method that returns a FundingCycle's properties as an array.
     *
     * Default is to not localize time stamps.
     *
     * @param array   $properties         An array listing the properties to include.
     * @param boolean $localizeTimeStamps A flag to inidcate whether or not to localize time stamps.
     *
     * @return array An array of property values for this FundingCycle.
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
                case 'url':
                    $personArray[] = $this->getUrl();
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

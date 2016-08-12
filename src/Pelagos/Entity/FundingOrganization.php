<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use Hateoas\Configuration\Annotation as Hateoas;

use Pelagos\Exception\NotDeletableException;

/**
 * Entity class to represent a Funding Organization.
 *
 * @ORM\Entity
 *
 * @UniqueEntity(
 *     fields={"name"},
 *     errorPath="name",
 *     message="A Funding Organization with this name already exists"
 * )
 *
 * @Hateoas\Relation(
 *   "self",
 *   href = @Hateoas\Route(
 *     "pelagos_api_funding_organizations_get",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "edit",
 *   href = @Hateoas\Route(
 *     "pelagos_api_funding_organizations_put",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not service('security.authorizationchecker').isGranted(['CAN_EDIT'], object))"
 *   )
 * )
 * @Hateoas\Relation(
 *   "delete",
 *   href = @Hateoas\Route(
 *     "pelagos_api_funding_organizations_delete",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not object.isDeletable() or not service('security.authorizationchecker').isGranted(['CAN_DELETE'], object))"
 *   )
 * )
 */
class FundingOrganization extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Funding Organization';

    /**
     * Name of a funding organization.
     *
     * @var string $name
     *
     * @access protected
     *
     * @ORM\Column(type="citext", unique=true)
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
     * @var string|resource $logo
     *
     * @access protected
     *
     * @ORM\Column(type="blob", nullable=true)
     */
    protected $logo;

    /**
     * Funding organization's email address.
     *
     * @var string $emailAddress
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
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
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
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
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
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
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
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
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
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
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
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
     * Funding organization's postal code (zipcode).
     *
     * @var string $postalCode
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
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
     *
     * @access protected
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\NoAngleBrackets(
     *     message="Country cannot contain angle brackets (< or >)"
     * )
     */
    protected $country;

    /**
     * Funding organization's Funding Cycle's.
     *
     * @var FundingCycle
     *
     * @access protected
     *
     * @ORM\OneToMany(targetEntity="FundingCycle", mappedBy="fundingOrganization")
     *
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $fundingCycles;

    /**
     * Funding Organization's PersonFundingOrganizations.
     *
     * @var \Doctrine\Common\Collections\Collection $personFundingOrganizations
     *
     * @access protected
     *
     * @ORM\OneToMany(targetEntity="PersonFundingOrganization", mappedBy="fundingOrganization")
     */
    protected $personFundingOrganizations;

    /**
     * This FundingOrganization's parent DataRepository.
     *
     * @var DataRepository $dataRepository
     *
     * @access protected
     *
     * @ORM\ManyToOne(targetEntity="DataRepository", inversedBy="fundingOrganizations")
     *
     * @Assert\NotBlank(
     *     message="Data Repository is required"
     * )
     */
    protected $dataRepository;

    /**
     * Getter for fundingCycles.
     *
     * @access public
     *
     * @return string String containing fundingCycles of funding organization.
     */
    public function getFundingCycles()
    {
        return $this->fundingCycles;
    }

    /**
     * Setter for fundingCycles.
     *
     * @param array|\Traversable $fundingCycles Set of FundingCycle objects.
     *
     * @access public
     *
     * @throws \Exception When Non-funding cycle found in $fundingCycles.
     * @throws \Exception When $fundingCycles is not an array or traversable object.
     *
     * @return void
     */
    public function setFundingCycles($fundingCycles)
    {
        if (is_array($fundingCycles) || $fundingCycles instanceof \Traversable) {
            $this->fundingCycles = $fundingCycles;
            foreach ($fundingCycles as $fundingCycle) {
                if (!$fundingCycle instanceof FundingCycle) {
                    throw new \Exception('Non-funding cycle found in FundingCycles');
                }
                $fundingCycle->setFundingOrganization($this);
            }
        } else {
            throw new \Exception('Funding Cycles must be array or traversable objects');
        }
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
     * @param string|resource $logo Containing byte string of logo.
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
     * @param boolean $asStream Whether to return the logo as a stream.
     *
     * @access public
     *
     * @return string|resource Binary string containing the logo or a stream resource pointing to it.
     */
    public function getLogo($asStream = false)
    {
        if ($asStream) {
            if (is_resource($this->logo) and get_resource_type($this->logo) == 'stream') {
                return $this->logo;
            } else {
                return null;
            }
        }
        if (is_resource($this->logo) and get_resource_type($this->logo) == 'stream') {
            rewind($this->logo);
            return stream_get_contents($this->logo);
        }
        return $this->logo;
    }

    /**
     * Get the mime type of logo.
     *
     * @access public
     *
     * @return string The mime type of logo.
     */
    public function getLogoMimeType()
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($this->getLogo());
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
     * Setter for personFundingOrganizations.
     *
     * @param array|\Traversable $personFundingOrganizations Set of PersonFundingOrganization objects.
     *
     * @access public
     *
     * @throws \Exception When $personFundingOrganizations is not an array or traversable object.
     * @throws \Exception When Non-PersonFundingOrganization found within $personFundingOrganizations.
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
                $personFundingOrganization->setFundingOrganization($this);
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
     *                                                 listings for this Funding Organization.
     */
    public function getPersonFundingOrganizations()
    {
        return $this->personFundingOrganizations;
    }

    /**
     * Setter for dataRepository.
     *
     * @param DataRepository $dataRepository An instance of \Pelagos\Entity\DataRepository.
     *
     * @access public
     *
     * @return void
     */
    public function setDataRepository(DataRepository $dataRepository = null)
    {
        $this->dataRepository = $dataRepository;
        if ($dataRepository !== null) {
            $this->dataRepository->addFundingOrganization($this);
        }
    }

    /**
     * Getter for dataRepository.
     *
     * @access public
     *
     * @return DataRepository instance representing the parent data repository.
     */
    public function getDataRepository()
    {
        return $this->dataRepository;
    }

    /**
     * Check if this FundingOrganization is deletable.
     *
     * This method throws a NotDeletableException when the FundingOrganization has associated
     * FundingCycles or Persons. The NotDeletableException will have its reasons set to a list of
     * reasons the FundingOrganization is not deletable.
     *
     * @throws NotDeletableException When the FundingOrganization has associated FundingCycles or Persons.
     *
     * @return void
     */
    public function checkDeletable()
    {
        $notDeletableReasons = array();
        $fundingCycleCount = count($this->getFundingCycles());
        if ($fundingCycleCount > 0) {
            $notDeletableReasons[] = 'there ' . ($fundingCycleCount > 1 ? 'are' : 'is') .
                " $fundingCycleCount associated Funding Cycle" .
                ($fundingCycleCount > 1 ? 's' : '');
        }
        $personFundingOrganizationCount = count($this->getPersonFundingOrganizations());
        if ($personFundingOrganizationCount > 0) {
            $notDeletableReasons[] = 'there ' . ($personFundingOrganizationCount > 1 ? 'are' : 'is') .
                " $personFundingOrganizationCount associated Person" .
                ($personFundingOrganizationCount > 1 ? 's' : '');
        }
        if (count($notDeletableReasons) > 0) {
            $notDeletableException = new NotDeletableException();
            $notDeletableException->setReasons($notDeletableReasons);
            throw $notDeletableException;
        }
    }
}

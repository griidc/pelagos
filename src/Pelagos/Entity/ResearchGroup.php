<?php

namespace Pelagos\Entity;

use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use Hateoas\Configuration\Annotation as Hateoas;

use JMS\Serializer\Annotation as Serializer;

use Pelagos\Exception\NotDeletableException;

/**
 * Entity class to represent a Research Group.
 *
 * @ORM\Entity
 *
 * @Assert\GroupSequence({
 *     "id",
 *     "unique_id",
 *     "ResearchGroup",
 *     "Entity",
 * })
 *
 * @UniqueEntity(
 *     fields={"name", "fundingCycle"},
 *     errorPath="name",
 *     message="A Research Group with this name already exists"
 * )
 *
 * @Hateoas\Relation(
 *   "self",
 *   href = @Hateoas\Route(
 *     "pelagos_api_research_groups_get",
 *     parameters = { "id" = "expr(object.getId())" }
 *   )
 * )
 * @Hateoas\Relation(
 *   "edit",
 *   href = @Hateoas\Route(
 *     "pelagos_api_research_groups_put",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not service('security.authorization_checker').isGranted(['CAN_EDIT'], object))"
 *   )
 * )
 * @Hateoas\Relation(
 *   "delete",
 *   href = @Hateoas\Route(
 *     "pelagos_api_research_groups_delete",
 *     parameters = { "id" = "expr(object.getId())" }
 *   ),
 *   exclusion = @Hateoas\Exclusion(
 *     excludeIf = "expr(not object.isDeletable() or not service('security.authorization_checker').isGranted(['CAN_DELETE'], object))"
 *   )
 * )
 */
class ResearchGroup extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Research Group';

    /**
     * Name of a research group.
     *
     * @var string $name
     *
     * @access protected
     *
     * @ORM\Column(type="citext", options={"collation":"POSIX"})
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
     * Research group's parent Funding Cycle.
     *
     * @var FundingCycle $fundingCycle
     *
     * @access protected
     *
     * @ORM\ManyToOne(targetEntity="FundingCycle", inversedBy="researchGroups")
     *
     * @Assert\NotBlank(
     *     message="Funding Cycle is required"
     * )
     *
     * @Serializer\MaxDepth(2)
     */
    protected $fundingCycle;

    /**
     * Research group's Website url.
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
     * Research group's telephone number.
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
     * Research group's delivery point (street address).
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
     * Research group's city.
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
     * Research group's administrative area (state).
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
     * Research group's postal code (zipcode).
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
     * Research group's country.
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
     * Description of a research group.
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
     * Research group's logo.
     *
     * @var string|resource $logo
     *
     * @access protected
     *
     * @ORM\Column(type="blob", nullable=true)
     */
    protected $logo;

    /**
     * Research group's email address.
     *
     * @var string $emailAddress
     *
     * @access protected
     *
     * @ORM\Column(type="citext", nullable=true)
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
     * Research group's PersonResearchGroups.
     *
     * @var \Doctrine\Common\Collections\Collection $personResearchGroups
     *
     * @access protected
     *
     * @ORM\OneToMany(targetEntity="PersonResearchGroup", mappedBy="researchGroup")
     *
     * @Serializer\MaxDepth(2)
     */
    protected $personResearchGroups;

    /**
     * Research group's list of Datasets.
     *
     * @var Collection $datasets
     *
     * @ORM\OneToMany(targetEntity="Dataset", mappedBy="researchGroup")
     *
     * @ORM\OrderBy({"udi" = "ASC"})
     *
     * @Serializer\Exclude
     */
    protected $datasets;

    /**
     * Getter for Datasets.
     *
     * @return Collection A Collection of Datasets.
     */
    public function getDatasets()
    {
        return $this->datasets;
    }

    /**
     * Serializer for the datasets virtual property.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("datasets")
     *
     * @return array
     */
    public function serializeDatasets()
    {
        $datasets = array();
        foreach ($this->datasets as $dataset) {
            $datasetArray = array(
                'id' => $dataset->getId(),
                'title' => $dataset->getTitle(),
                'udi' => $dataset->getUdi(),
            );
            if (null !== $dataset->getDif()) {
                $datasetArray['dif'] = array(
                    'id' => $dataset->getDif()->getId(),
                    'status' => $dataset->getDif()->getStatus(),
                    'title' => $dataset->getDif()->getTitle(),
                );
            } else {
                $datasetArray['dif'] = null;
            }
            $datasets[] = $datasetArray;
        }
        return $datasets;
    }

    /**
     * Setter for name.
     *
     * @param string $name Textual name of research group.
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
     * @return string String containing name of research group.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter for fundingCycle.
     *
     * @param FundingCycle $fundingCycle The FundingCycle to associate this ResearchGroup with.
     *
     * @access public
     *
     * @return void
     */
    public function setFundingCycle(FundingCycle $fundingCycle = null)
    {
        $this->fundingCycle = $fundingCycle;
    }

    /**
     * Getter for fundingCycles.
     *
     * @access public
     *
     * @return string String containing fundingCycles of research group.
     */
    public function getFundingCycle()
    {
        return $this->fundingCycle;
    }

    /**
     * Setter for url.
     *
     * @param string $url Research group's Website URL.
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
     * @return string URL of research group's Website.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for phoneNumber.
     *
     * @param string $phoneNumber Research group's phone number.
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
     * @return string Phone number of research group.
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Setter for deliveryPoint.
     *
     * @param string $deliveryPoint Street address of research group.
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
     * @return string Street address of research group.
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * Setter for city.
     *
     * @param string $city City of research group.
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
     * @return string City of research group.
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Setter for administrativeArea.
     *
     * @param string $administrativeArea Research group's administrative area (state).
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
     * @return string Research group's administrative area (state).
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
     * @param string $country Research group's country.
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
     * @return string Research group's country.
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Setter for description.
     *
     * @param string $description Description of research group.
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
     * @return string Description of research group.
     */
    public function getDescription()
    {
        return $this->description;
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
     * Setter for emailAddress.
     *
     * @param string $emailAddress Containing email address of research group.
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
            $this->personResearchGroups = $personResearchGroups;
            foreach ($personResearchGroups as $personResearchGroup) {
                if (!$personResearchGroup instanceof PersonResearchGroup) {
                    throw new \Exception('Non-PersonResearchGroup found in personResearchGroups.');
                }
                $personResearchGroup->setResearchGroup($this);
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
     * Check if this ResearchGroup is deletable.
     *
     * This method throws a NotDeletableException when the ResearchGroup has associated
     * Persons. The NotDeletableException will have its reasons set to a list of
     * reasons the ResearchGroup is not deletable.
     *
     * @throws NotDeletableException When the ResearchGroup has associated Persons.
     *
     * @return void
     */
    public function checkDeletable()
    {
        $notDeletableReasons = array();
        $personResearchGroupCount = count($this->getPersonResearchGroups());
        if ($personResearchGroupCount > 0) {
            $notDeletableReasons[] = 'there ' . ($personResearchGroupCount > 1 ? 'are' : 'is') .
                " $personResearchGroupCount associated Person" .
                ($personResearchGroupCount > 1 ? 's' : '');
        }
        if (count($notDeletableReasons) > 0) {
            $notDeletableException = new NotDeletableException();
            $notDeletableException->setReasons($notDeletableReasons);
            throw $notDeletableException;
        }
    }

    /**
     * Compare two Research Groups by name.
     *
     * @param ResearchGroup $a First Research Group to compare.
     * @param ResearchGroup $b Second Research Group to compare.
     *
     * @return integer
     */
    public static function compareByName(ResearchGroup $a, ResearchGroup $b)
    {
        return strcmp($a->getName(), $b->getName());
    }
}

<?php

namespace App\Entity;

use App\Repository\InformationProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Information Product Entity class.
 */
#[ORM\Entity(repositoryClass: InformationProductRepository::class)]
class InformationProduct
{
    use EntityTrait;
    use EntityIdTrait;
    use EntityDateTimeTrait;

    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Information Product';

    /**
     * The title of the Information Product.
     *
     * @var string
     *
     *
     */
    #[ORM\Column(type: 'text')]
    #[Serializer\Groups(['card'])]
    #[Assert\NotBlank(message: 'A title is required.')]
    private $title;

    /**
     * The creators of the Information Product.
     *
     * @var string
     *
     *
     */
    #[ORM\Column(type: 'text')]
    #[Serializer\Groups(['card'])]
    #[Assert\NotBlank(message: 'A creator is required.')]
    private $creators;

    /**
     * The publisher of the Information Product.
     *
     * @var string
     *
     *
     *
     *
     */
    #[ORM\Column(type: 'text')]
    #[Serializer\Groups(['card'])]
    #[Assert\NotBlank(message: 'A publisher is required.')]
    private $publisher;

    /**
     * An external DOI for the Information Product.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Serializer\Groups(['card'])]
    private $externalDoi;

    /**
     * Is the Information Product published.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    #[Serializer\Groups(['search'])]
    private $published = false;

    /**
     * Is the Information Product remotely hosted.
     *
     * @var boolean
     */
    #[ORM\Column(type: 'boolean')]
    #[Serializer\Groups(['search', 'card'])]
    private $remoteResource = false;

    /**
     * The research groups this Information Product is associated with.
     *
     * @var Collection
     *
     */
    #[ORM\ManyToMany(targetEntity: ResearchGroup::class)]
    #[Serializer\MaxDepth(1)]
    #[Serializer\SerializedName('researchGroup')]
    #[Serializer\Groups(['search'])]
    private $researchGroups;

    /**
     * The remote uri for this Information Product.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    #[Serializer\Groups(['search', 'card'])]
    private $remoteUri;

    /**
     * The file for this Information Product.
     *
     * @var File
     */
    #[ORM\OneToOne(targetEntity: File::class, cascade: ['persist', 'remove'])]
    #[Serializer\MaxDepth(1)]
    #[Serializer\Groups(['search', 'card'])]
    private $file;

    /**
     * The collection of Product Types for this Information Product.
     *
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: ProductTypeDescriptor::class)]
    #[Serializer\MaxDepth(1)]
    #[Serializer\Groups(['search'])]
    private $productTypeDescriptors;

    /**
     * The collection of Digital Resource Types for this Information Product.
     *
     * @var Collection
     */
    #[ORM\ManyToMany(targetEntity: DigitalResourceTypeDescriptor::class)]
    #[Serializer\MaxDepth(1)]
    #[Serializer\Groups(['search'])]
    private $digitalResourceTypeDescriptors;

    #[ORM\ManyToMany(targetEntity: Funder::class)]
    private Collection $funders;

    /**
     * Constructor.
     *
     * Initializes collections to empty collections.
     */
    public function __construct()
    {
        $this->researchGroups = new ArrayCollection();
        $this->productTypeDescriptors = new ArrayCollection();
        $this->digitalResourceTypeDescriptors = new ArrayCollection();
        $this->funders = new ArrayCollection();
    }

    /**
     * Get the title for this Information Product.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the title for this Information Product.
     *
     * @param string|null $title The title for this Information Product.
     *
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the creators for this Information Product.
     *
     * @return string|null
     */
    public function getCreators(): ?string
    {
        return $this->creators;
    }

    /**
     * Set the creators of the Information Product.
     *
     * @param string $creators The creators of the Information Product.
     *
     * @return self
     */
    public function setCreators(string $creators): self
    {
        $this->creators = $creators;

        return $this;
    }

    /**
     * Get the publisher of the Information Product.
     *
     * @return string|null
     */
    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    /**
     * Set the publisher of the Information Product.
     *
     * @param string $publisher
     * @return self
     */
    public function setPublisher(string $publisher): self
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Get the External DOI.
     *
     * @return string|null
     */
    public function getExternalDoi(): ?string
    {
        return $this->externalDoi;
    }

    /**
     * Set the external DOI.
     *
     * @param string|null $externalDoi
     *
     * @return self
     */
    public function setExternalDoi(?string $externalDoi): self
    {
        $this->externalDoi = $externalDoi;

        return $this;
    }

    /**
     * Is the Information Product published?
     *
     * @return boolean|null
     */
    public function isPublished(): ?bool
    {
        return $this->published;
    }

    /**
     * Set the published state of the Information Product.
     *
     * @param boolean $published
     *
     * @return self
     */
    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get the remote resource flag of this Information Product.
     *
     * @return boolean|null
     */
    public function getRemoteResource(): ?bool
    {
        return $this->remoteResource;
    }

    /**
     * Set the remote resource flag of this Information Product.
     *
     * @param boolean $remoteResource
     *
     * @return self
     */
    public function setRemoteResource(bool $remoteResource): self
    {
        $this->remoteResource = $remoteResource;

        return $this;
    }

    /**
     * Get the Research Groups for this Information Product.
     *
     * @return Collection|ResearchGroup[]
     */
    public function getResearchGroups(): Collection
    {
        return $this->researchGroups;
    }

    /**
     * Get the Research Groups List for this Information Product.
     *
     *
     * @return array
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('researchGroups')]
    public function getResearchGroupList(): array
    {
        $researchGroupList = [];

        foreach ($this->getResearchGroups() as $researchGroup) {
            $researchGroupList[] = $researchGroup->getId();
        }

        return $researchGroupList;
    }


    /**
     * Add a Research Group to this Information Product.
     *
     * @param ResearchGroup $researchGroup
     *
     * @return self
     */
    public function addResearchGroup(ResearchGroup $researchGroup): self
    {
        if (!$this->researchGroups->contains($researchGroup)) {
            $this->researchGroups[] = $researchGroup;
        }

        return $this;
    }

    /**
     * Remove a Research Group from this Information Product.
     *
     * @param ResearchGroup $researchGroup
     *
     * @return self
     */
    public function removeResearchGroup(ResearchGroup $researchGroup): self
    {
        $this->researchGroups->removeElement($researchGroup);

        return $this;
    }

    /**
     * Get the Remote URI for this Information Product.
     *
     * @return string|null
     */
    public function getRemoteUri(): ?string
    {
        return $this->remoteUri;
    }

    /**
     * Set the Remote URI for this Information Product.
     *
     * @param string|null $remoteUri
     * @return self
     */
    public function setRemoteUri(?string $remoteUri): self
    {
        $this->remoteUri = $remoteUri;

        return $this;
    }

    /**
     * Getter for Remote URI host name.
     *
     *
     * @return string|null
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('remoteUriHostName')]
    #[Serializer\Groups(['card'])]
    public function getRemoteUriHostName(): ?string
    {
        $remoteUri = $this->getRemoteUri();
        if (!empty($remoteUri)) {
            return parse_url($remoteUri, PHP_URL_HOST);
        }
        return null;
    }

    /**
     * Get the file for this Information Product.
     *
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * Set the file for this Information Product.
     *
     * @param File|null $file
     * @return self
     */
    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Adder for Information Product type.
     *
     * @param ProductTypeDescriptor $productTypeDescriptor Single information product type to be added.
     *
     * @return void
     */
    public function addProductTypeDescriptor(ProductTypeDescriptor $productTypeDescriptor): void
    {
        if (!$this->productTypeDescriptors->contains($productTypeDescriptor)) {
            $this->productTypeDescriptors->add($productTypeDescriptor);
        }
    }

    /**
     * Remover for Information Product type.
     *
     * @param ProductTypeDescriptor $productTypeDescriptor Single information product type to be removed.
     *
     * @return void
     */
    public function removeProductTypeDescriptor(ProductTypeDescriptor $productTypeDescriptor): void
    {
        $this->productTypeDescriptors->removeElement($productTypeDescriptor);
    }

    /**
     * Get all Digitial Resource Type Descriptors.
     *
     * @return Collection|DigitalResourceTypeDescriptor[]
     */
    public function getDigitalResourceTypeDescriptors(): Collection
    {
        return $this->digitalResourceTypeDescriptors;
    }

    /**
     * Add Digital Resource Type Descriptor to Information Product.
     *
     * @param DigitalResourceTypeDescriptor $digitalResourceTypeDescriptor
     *
     * @return self
     */
    public function addDigitalResourceTypeDescriptor(DigitalResourceTypeDescriptor $digitalResourceTypeDescriptor): self
    {
        if (!$this->digitalResourceTypeDescriptors->contains($digitalResourceTypeDescriptor)) {
            $this->digitalResourceTypeDescriptors->add($digitalResourceTypeDescriptor);
        }

        return $this;
    }

    /**
     * Remove Digital Resource Type Descriptor to Information Product.
     *
     * @param DigitalResourceTypeDescriptor $digitalResourceTypeDescriptor
     *
     * @return self
     */
    public function removeDigitalResourceTypeDescriptor(DigitalResourceTypeDescriptor $digitalResourceTypeDescriptor): self
    {
        $this->digitalResourceTypeDescriptors->removeElement($digitalResourceTypeDescriptor);

        return $this;
    }

    /**
     * Get the List of Product type descriptors for this Information Product.
     *
     *
     * @return array
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('productTypeDescriptorsList')]
    public function getProductTypeDescriptorList(): array
    {
        $productTypeDescriptorList = [];

        foreach ($this->getProductTypeDescriptors() as $productTypeDescriptor) {
            $productTypeDescriptorList[] = $productTypeDescriptor->getId();
        }
        return $productTypeDescriptorList;
    }

    /**
     * Get the Product type descriptors for this Information Product.
     *
     * @return Collection|ResearchGroup[]
     */
    public function getProductTypeDescriptors(): Collection
    {
        return $this->productTypeDescriptors;
    }


    /**
     * Get the List of Digital Resource type descriptors for this Information Product.
     *
     *
     * @return array
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('digitalResourceTypeDescriptorsList')]
    public function getDigitalResourceTypeDescriptorList(): array
    {
        $digitalResourceTypeDescriptorList = [];

        foreach ($this->getDigitalResourceTypeDescriptors() as $digitalResourceTypeDescriptor) {
            $digitalResourceTypeDescriptorList[] = $digitalResourceTypeDescriptor->getId();
        }
        return $digitalResourceTypeDescriptorList;
    }

    /**
     * Show friendly name of this entity.
     *
     *
     * @return string
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('friendlyName')]
    #[Serializer\Groups(['search'])]
    public function getFriendlyName(): string
    {
        return $this::FRIENDLY_NAME;
    }

    /**
     * Show class name of this entity.
     *
     *
     * @return string
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('className')]
    #[Serializer\Groups(['search'])]
    public function getClassName(): string
    {
        return get_class($this);
    }

    /**
     *Get the Funder List for this Information Product.
     *
     *
     * @return array
     */
    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('funders')]
    public function getFunderList(): array
    {
        $funderList = [];

        foreach ($this->getFunders() as $funder) {
            $funderList[] = $funder->getId();
        }

        return $funderList;
    }

    /**
     * Get all funders.
     *
     * @return Collection<int, Funder>
     */
    public function getFunders(): Collection
    {
        return $this->funders;
    }

    /**
     * Add Funder to Information Product.
     */
    public function addFunder(Funder $funder): self
    {
        if (!$this->funders->contains($funder)) {
            $this->funders->add($funder);
        }

        return $this;
    }

    /**
     * Remove Funder from Information Product.
     */
    public function removeFunder(Funder $funder): self
    {
        $this->funders->removeElement($funder);

        return $this;
    }
}

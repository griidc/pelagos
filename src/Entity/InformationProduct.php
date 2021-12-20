<?php

namespace App\Entity;

use App\Repository\InformationProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Information Product Entity class.
 *
 * @ORM\Entity(repositoryClass=InformationProductRepository::class)
 */
class InformationProduct extends Entity
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Information Product';

    /**
     * The title of the Information Product.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(
     *     message="A title is required."
     * )
     */
    private $title;

    /**
     * The creators of the Information Product.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(
     *     message="A creator is required."
     * )
     */
    private $creators;

    /**
     * The publisher of the Information Product.
     *
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(
     *     message="A publisher is required."
     *
     */
    private $publisher;

    /**
     * An external DOI for the Information Product.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $externalDoi;

    /**
     * Is the Information Product published.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $published = false;

    /**
     * Is the Information Product remotely hosted.
     *
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $remoteResource = false;

    /**
     * The research groups this Information Product is associated with.
     *
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity=ResearchGroup::class)
     */
    private $researchGroups;

    /**
     * Constructor.
     *
     * Initializes collections to empty collections.
     */
    public function __construct()
    {
        $this->researchGroups = new ArrayCollection();
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
     * @param string|null $shortTitle The title for this Information Product.
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
     * @return self
     */
    public function setExternalDoi(?string $externalDoi): self
    {
        $this->externalDoi = $externalDoi;

        return $this;
    }

    /**
     * Is the Information Producted published?
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
     * Add a Research Group to this Information Product.
     *
     * @param ResearchGroup $researchGroup
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
     * @return self
     */
    public function removeResearchGroup(ResearchGroup $researchGroup): self
    {
        $this->researchGroups->removeElement($researchGroup);

        return $this;
    }
}

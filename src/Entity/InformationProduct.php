<?php

namespace App\Entity;

use App\Repository\InformationProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InformationProductRepository::class)
 */
class InformationProduct extends Entity
{
    /**
     * @ORM\Column(type="text")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     */
    private $creators;

    /**
     * @ORM\Column(type="text")
     */
    private $publisher;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $externalDoi;

    /**
     * @ORM\Column(type="boolean")
     */
    private $published;

    /**
     * @ORM\Column(type="boolean")
     */
    private $remoteResource;

    /**
     * @ORM\ManyToMany(targetEntity=ResearchGroup::class)
     */
    private $researchGroups;

    public function __construct()
    {
        $this->researchGroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCreators(): ?string
    {
        return $this->creators;
    }

    public function setCreators(string $creators): self
    {
        $this->creators = $creators;

        return $this;
    }

    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    public function setPublisher(string $publisher): self
    {
        $this->publisher = $publisher;

        return $this;
    }

    public function getExternalDoi(): ?string
    {
        return $this->externalDoi;
    }

    public function setExternalDoi(?string $externalDoi): self
    {
        $this->externalDoi = $externalDoi;

        return $this;
    }

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getRemoteResource(): ?bool
    {
        return $this->remoteResource;
    }

    public function setRemoteResource(bool $remoteResource): self
    {
        $this->remoteResource = $remoteResource;

        return $this;
    }

    /**
     * @return Collection|ResearchGroup[]
     */
    public function getResearchGroups(): Collection
    {
        return $this->researchGroups;
    }

    public function addResearchGroup(ResearchGroup $researchGroup): self
    {
        if (!$this->researchGroups->contains($researchGroup)) {
            $this->researchGroups[] = $researchGroup;
        }

        return $this;
    }

    public function removeResearchGroup(ResearchGroup $researchGroup): self
    {
        $this->researchGroups->removeElement($researchGroup);

        return $this;
    }
}

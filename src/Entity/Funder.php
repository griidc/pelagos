<?php

namespace App\Entity;

use App\Repository\FunderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Funder class.
 *
 * @ORM\Entity(repositoryClass="App\Repository\FunderRepository")
 */
class Funder extends Entity
{
    /**
     * Name of the Funder.
     *
     * @ORM\Column(type="text")
     */
    private ?string $name = null;

    /**
     * Reference URI of the Funder.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $referenceUri = null;

    /**
     * The datasets associated with this Funder
     *
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Dataset", mappedBy="funders", cascade={"persist"})
     */
    private Collection $datasets;

    public function __construct()
    {
        $this->datasets = new ArrayCollection();
    }

    /**
     * Gets name of the funder.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets name of the funder.
     *
     * @param string $name name of the funder
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the reference URI for the Funder.
     */
    public function getReferenceUri(): ?string
    {
        return $this->referenceUri;
    }

    /**
     * Sets the reference URI for the Funder.
     *
     * @param string $referenceUri the reference URI for the Funder
     */
    public function setReferenceUri(?string $referenceUri): self
    {
        $this->referenceUri = $referenceUri;

        return $this;
    }
}

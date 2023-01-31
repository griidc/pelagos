<?php

namespace App\Entity;

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

    /**
     * Get string version of Funder (name).
     */
    public function __toString(): string
    {
        return $this->getName() ?? 'UNKNOWN FUNDER';
    }
}

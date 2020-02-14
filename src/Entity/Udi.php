<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Entity class to keep list of issued UDI's.
 *
 * @ORM\Entity(repositoryClass="App\Repository\UdiRepository")
 *
 * @UniqueEntity(
 *     fields={"uniqueDataIdentifier"},
 *     errorPath="uniqueDataIdentifier",
 *     message="This UDI already exists"
 * )
 *
 */
class Udi extends Entity
{
    /**
     * A string containing the UDI.
     *
     * @ORM\Column(type="text", nullable=false, unique=true)
     */
    protected $uniqueDataIdentifier;

    /**
     * Get the UDI.
     *
     * @return string The clear text password.
     */
    public function getUniqueDataIdentifier(): string
    {
        return $this->uniqueDataIdentifier;
    }

    /**
     * Set the UDI.
     *
     * @param string $uniqueDataIdentifier The UDi string.
     *
     * @return self
     */
    public function setUniqueDataIdentifier(string $uniqueDataIdentifier): self
    {
        $this->uniqueDataIdentifier = $uniqueDataIdentifier;

        return $this;
    }
}

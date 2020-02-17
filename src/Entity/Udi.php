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
     * Constructor.
     *
     * @param string $uniqueDataIdentifier The UDI string.
     *
     * Sets to UDI identifier for the UDI entity.
     */
    public function __construct(string $uniqueDataIdentifier): void
    {
        $this->uniqueDataIdentifier = $uniqueDataIdentifier;
    }

    /**
     * A Pretty Print sort of formatting in the string context.
     *
     * @return string The formatted UDI string.
     */
    public function __toString(): string
    {
        return (string) $this->uniqueDataIdentifier;
    }
}

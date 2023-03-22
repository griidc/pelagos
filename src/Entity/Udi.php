<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Entity class to keep list of issued UDI's.
 *
 *
 * @UniqueEntity(
 *     fields={"uniqueDataIdentifier"},
 *     errorPath="uniqueDataIdentifier",
 *     message="This UDI already exists"
 * )
 */
#[ORM\Entity(repositoryClass: 'App\Repository\UdiRepository')]
class Udi
{
    /**
     * A string containing the UDI.
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: false, unique: true)]
    #[ORM\Id]
    protected $uniqueDataIdentifier;

    /**
     * Constructor.
     *
     * @param string $uniqueDataIdentifier The UDI string.
     *
     * Sets to UDI identifier for the UDI entity.
     */
    public function __construct(string $uniqueDataIdentifier)
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

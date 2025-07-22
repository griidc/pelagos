<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Trait for description type.
 */
trait DescriptorTypeTrait
{
    #[ORM\Column(type: 'text')]
    #[Serializer\Groups(['search'])]
    #[Serializer\SerializedName('description')]
    private $description;

    /**
     * Getter for description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Setter for description.
     *
     * @param string $description Description type string.
     *
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getDescription() ?? '';
    }
}

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait for description type.
 */
trait DescriptorTypeTrait
{
    #[ORM\Column(type: 'text')]
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
}

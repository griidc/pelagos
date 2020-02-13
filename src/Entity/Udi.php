<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UdiRepository")
 *
 * @UniqueEntity(
 *     fields={"emailAddress"},
 *     errorPath="emailAddress",
 *     message="A Person with this email address already exists"
 * )
 *
 */
class Udi extends Entity
{
    /**
     * @ORM\Column(type="text")
     */
    private $uniqueDataIdentifier;

    public function getUniqueDataIdentifier(): ?string
    {
        return $this->uniqueDataIdentifier;
    }

    public function setUniqueDataIdentifier(string $uniqueDataIdentifier): self
    {
        $this->uniqueDataIdentifier = $uniqueDataIdentifier;

        return $this;
    }
}

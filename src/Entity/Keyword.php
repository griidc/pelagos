<?php

namespace App\Entity;

use App\Enum\KeywordType;
use App\Repository\KeywordRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Keywords class for Standardized keywords.
 */
#[ORM\Entity(repositoryClass: KeywordRepository::class)]
class Keyword extends Entity
{
    /**
     * JSON containing Standard Keyword Data.
     */
    #[ORM\Column]
    private array $json = [];

    /**
     * Type of the Keyword.
     */
    #[ORM\Column(length: 255, enumType: KeywordType::class)]
    private ?KeywordType $type = null;

    /**
     * Get the JSON for this Keyword.
     */
    public function getJson(): array
    {
        return $this->json;
    }


    /**
     * Set the JSON for this Keyword.
     */
    public function setJson(array $json): self
    {
        $this->json = $json;

        return $this;
    }

    /**
     * Get the Type for this Keyword.
     */
    public function getType(): ?KeywordType
    {
        return $this->type;
    }

    /**
     * Set the Type for this Keyword.
     */
    public function setType(KeywordType $type): self
    {
        $this->type = $type;

        return $this;
    }
}

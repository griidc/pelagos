<?php

namespace App\Entity;

use App\Enum\KeywordType;
use App\Repository\KeywordRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Keywords class for Standardized keywords.
 */
#[ORM\Entity(repositoryClass: KeywordRepository::class)]
class Keyword extends Entity
{
    /**
     * JSON containing Standard Keyword Data.
     */
    #[ORM\Column(type: 'json')]
    private mixed $json = null;

    /**
     * Type of the Keyword.
     */
    #[ORM\Column(length: 255, enumType: KeywordType::class)]
    #[Serializer\Type(name: KeywordType::class)]
    private ?KeywordType $type = null;

    // #[ORM\Column(type: Types::TEXT)]
    // private ?string $identifier = null;

    // #[ORM\Column(type: Types::TEXT, nullable: true)]
    // private ?string $description = null;

    // #[ORM\Column(type: Types::TEXT)]
    // private ?string $label = null;

    // #[ORM\Column(type: Types::TEXT, nullable: true)]
    // private ?string $referenceUri = null;

    // #[ORM\Column(type: Types::TEXT, nullable: true)]
    // private ?string $parentIdentifier = null;

    // #[ORM\Column(type: Types::TEXT, nullable: true)]
    // private ?string $displayPath = null;

    /**
     * Get the JSON for this Keyword.
     */
    public function getJson(): mixed
    {
        return $this->json;
    }

    /**
     * Set the JSON for this Keyword.
     */
    public function setJson(mixed $json): self
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getReferenceUri(): ?string
    {
        return $this->referenceUri;
    }

    public function setReferenceUri(?string $referenceUri): self
    {
        $this->referenceUri = $referenceUri;

        return $this;
    }

    public function getParentIdentifier(): ?string
    {
        return $this->parentIdentifier;
    }

    public function setParentIdentifier(string $parentIdentifier): self
    {
        $this->parentIdentifier = $parentIdentifier;

        return $this;
    }

    public function getDisplayPath(): ?string
    {
        return $this->displayPath;
    }

    public function setDisplayPath(?string $displayPath): self
    {
        $this->displayPath = $displayPath;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }
}

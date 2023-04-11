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
     * Type of the Keyword.
     */
    #[ORM\Column(length: 255, enumType: KeywordType::class)]
    #[Serializer\Type(name: KeywordType::class)]
    private ?KeywordType $type = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $identifier = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $definition = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $referenceUri = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $parentUri = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $displayPath = null;

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

    public function getDefinition(): ?string
    {
        return $this->definition;
    }

    public function setDefinition(?string $definition): self
    {
        $this->definition = $definition;

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

    public function getParentUri(): ?string
    {
        return $this->parentUri;
    }

    public function setParentUri(?string $parentUri): self
    {
        $this->parentUri = $parentUri;

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

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
    use IdTrait;

    /**
     * Type of the Keyword.
     */
    #[ORM\Column(length: 255, enumType: KeywordType::class)]
    #[Serializer\Type(name: KeywordType::class)]
    private ?KeywordType $type = null;

    /**
     * Identifier for the Keyword.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Serializer\Groups(['api'])]
    private ?string $identifier = null;

    /**
     * Description for the Keyword.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Serializer\Groups(['api'])]
    private ?string $definition = null;

    /**
     * Display label for the Keyword.
     */
    #[ORM\Column(type: Types::TEXT)]
    #[Serializer\Groups(['api'])]
    private ?string $label = null;

    /**
     * Reference URI for the Keyword.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Serializer\Groups(['api'])]
    private ?string $referenceUri = null;

    /**
     * The parent URI for this Keyword.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Serializer\Groups(['api'])]
    private ?string $parentUri = null;

    /**
     * Breadcrumb part off all parent for the Keyword as display value.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $displayPath = null;

    /**
     * Property if this keyword node expanded by default.
     */
    #[ORM\Column(nullable: false)]
    #[Serializer\Groups(["api"])]
    private bool $expanded = false;

    /**
     * Does the item have and parents?
     */
    #[Serializer\VirtualProperty]
    #[Serializer\Groups(['api'])]
    #[Serializer\SerializedName('hasItems')]
    public function hasItems(): bool
    {
        return !(empty($this->parentUri));
    }

    /**
     * Display path with possible Science Keywords string removed.
     */
    #[Serializer\VirtualProperty]
    #[Serializer\Groups(["api"])]
    #[Serializer\SerializedName("displayPath")]
    public function getShortDisplayPath(): string
    {
        $displayPath = $this->getDisplayPath() ?? '';

        return preg_replace('/^Science Keywords( > )?/i', '', $displayPath);
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

    /**
     * Get the Definitaiton for this Keyword.
     */
    public function getDefinition(): ?string
    {
        return $this->definition;
    }

    /**
     * Set the definition for the Keyword.
     */
    public function setDefinition(?string $definition): self
    {
        $this->definition = $definition;

        return $this;
    }

    /**
     * Get the label for this Keyword.
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Set the label for this Keyword.
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the reference URI for this.
     */
    public function getReferenceUri(): ?string
    {
        return $this->referenceUri;
    }

    /**
     * Set the reference URI for this.
     */
    public function setReferenceUri(?string $referenceUri): self
    {
        $this->referenceUri = $referenceUri;

        return $this;
    }

    /**
     * Get the parent URI for the Keyword.
     */
    public function getParentUri(): ?string
    {
        return $this->parentUri;
    }

    /**
     * Get the parent URI for the Keyword.
     */
    public function setParentUri(?string $parentUri): self
    {
        $this->parentUri = $parentUri;

        return $this;
    }

    /**
     * Get the full display path for the Keyword.
     */
    public function getDisplayPath(): ?string
    {
        return $this->displayPath;
    }

    /**
     * Set the diplay path for the Keyword.
     */
    public function setDisplayPath(?string $displayPath): self
    {
        $this->displayPath = $displayPath;

        return $this;
    }

    /**
     * Get the identifier for the Keyword.
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * Set the identifier for the Keyword.
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * If the keyword is expanded.
     */
    public function isExpanded(): bool
    {
        return $this->expanded;
    }

    /**
     * Set keyword node to expanded.
     */
    public function setExpanded(bool $expanded): self
    {
        $this->expanded = $expanded;

        return $this;
    }

    /**
     * Gets the level of the keyword.
     *
     * @param boolean|null $correctForType If the keyword is type GCMD, then the first level doesn't count.
     */
    public function getLevel(?bool $correctForType = true): int
    {
        $levels = explode(' > ', $this->getDisplayPath() ?? '');
        if ($correctForType) {
            if ($this->getType() === KeywordType::TYPE_GCMD) {
                array_shift($levels);
            }
        }
        return count($levels);
    }

    public function getLevelTwo(): ?string
    {
        $levels = explode(' > ', $this->getDisplayPath() ?? '');
        if (count($levels) >= 2) {
            return $levels[0] . ' > ' . $levels[1];
        }
        return null;
    }

    /**
     * Stringifier, show label.
     */
    public function __toString(): string
    {
        return $this->getLabel() ?? '';
    }
}

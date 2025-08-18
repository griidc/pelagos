<?php

namespace App\Entity;

use App\Repository\FunderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Funder class.
 */
#[ORM\Entity(repositoryClass: FunderRepository::class)]
class Funder
{
    use EntityTrait;
    use EntityIdTrait;
    use EntityDateTimeTrait;

    public const SOURCE_IMPORTED = 'imported';
    public const SOURCE_USER = 'user';
    public const SOURCE_DRPM = 'drpm';

    public const SOURCES = [
        'Imported' => self::SOURCE_IMPORTED,
        'User' => self::SOURCE_USER,
        'DRPM' => self::SOURCE_DRPM,
    ];

    /**
     * Name of the Funder.
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $name = null;

    /**
     * The short name of the Funder
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $shortName = null;

    /**
     * Reference URI of the Funder.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $referenceUri = null;

    /**
     * The source of the Funder.
     */
    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $source = self::SOURCE_DRPM;

    /**
     * Gets name of the funder.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets name of the funder.
     *
     * @param string $name name of the funder
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /*
     * Gets the short name of the Funder.
     */
    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    /*
     * Sets the short name of the Funder.
     */
    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * Gets the reference URI for the Funder.
     */
    public function getReferenceUri(): ?string
    {
        return $this->referenceUri;
    }

    /**
     * Sets the reference URI for the Funder.
     *
     * @param string $referenceUri the reference URI for the Funder
     */
    public function setReferenceUri(?string $referenceUri): self
    {
        $this->referenceUri = $referenceUri;

        return $this;
    }

    /*
     * Set the source for this Funder.
     *
     * @see Funder::SOURCES
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Set the source for this Funder.
     *
     * @param string $source source for this Funder
     *
     * @see Funder::SOURCES
     *
     * @throws \InvalidArgumentException if the source is not a valid source
     */
    public function setSource(string $source): self
    {
        if (false === in_array($source, self::SOURCES)) {
            throw new \InvalidArgumentException('This is not a valid source');
        }

        $this->source = $source;

        return $this;
    }

    /*
     * Gets the name of the Funder.
     */
    public function __toString(): string
    {
        return $this->getName() ?? '';
    }
}

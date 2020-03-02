<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DatasetLinksRepository")
 */
class DatasetLinks extends Entity
{
    /**
     * Valid values for self::$temporalExtent.
     *
     * The array keys are the values to be set in self::temporalExtent.
     */
    const TEMPORAL_EXTENT_DESCRIPTIONS = [
        'ground condition' => [
            'name' => 'Ground Condition',
            'description' => 'Data represent the actual condition of things on the ground during ' .
                             'the time period specified and may also be used to characterize data ' .
                             'generated from a sample collection in the field when samples are subsequently ' .
                             'analyzed in a laboratory.'
        ],
        'modeled period' => [
            'name' => 'Modeled Period',
            'description' => 'Data represents simulated conditions during the time period, ' .
                             'and may be used to characterize data generated using a computational model.'
        ],
        'ground condition and modeled period' => [
            'name' => 'Ground Condition and Modeled Period',
            'description' => 'Both choices apply.'
        ],
    ];
    
    /**
     * @ORM\Column(type="text")
     */
    private $url;

    /**
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="integer")
     */
    private $functionCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $protocol;

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getfunctionCode(): ?int
    {
        return $this->functionCode;
    }

    public function setfunctionCode(int $functionCode): self
    {
        $this->functionCode = $functionCode;

        return $this;
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(?string $protocol): self
    {
        $this->protocol = $protocol;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\GCMDKeywordRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * The GCMDKeyword Class.
 */
#[ORM\Entity(repositoryClass: GCMDKeywordRepository::class)]
class GCMDKeyword extends Entity
{
    #[ORM\Column(type: Types::TEXT)]
    private ?string $keywordJson = null;

    public function getKeywordJson(): ?string
    {
        return $this->keywordJson;
    }

    public function setKeywordJson(string $keywordJson): self
    {
        $this->keywordJson = $keywordJson;

        return $this;
    }
}

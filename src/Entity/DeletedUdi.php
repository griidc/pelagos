<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DeletedUdi Entity class.
 */
#[ORM\Entity()]
class DeletedUdi extends Entity
{
    /**
     * UDI as string
     *
     * @var string
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $udi;

    public function getUDI(): ?string
    {
        return $this->udi;
    }

    public function setUDI(string $udi): self
    {
        $this->udi = $udi;
        return $this;
    }
}

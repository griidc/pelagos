<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait EntityIdTrait
{
    #[ORM\Id]
    #[ORM\Column(type:"integer")]
    #[ORM\GeneratedValue(strategy:"AUTO")]
    #[Serializer\Groups(["id", "search"])]
    #[Groups(["id", "search"])]
    #[Assert\Range(min: 1, max: 2147483647, notInRangeMessage: 'ID must be in between 1 and 2147483647', invalidMessage: 'ID must be a positive integer', groups: ['id'])]
    public $id;

    /**
     * Getter for the ID.
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}
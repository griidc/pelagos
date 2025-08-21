<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

trait IdTrait
{
    /**
     * Entity identifier.
     *
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[Serializer\Groups(["id", "search"])]
    #[Groups(["id", "search"])]
    #[Assert\Range(min: 1, max: 2147483647, notInRangeMessage: 'ID must be in between 1 and 2147483647', invalidMessage: 'ID must be a positive integer', groups: ['id'])]
    protected $id;

    /**
     * Getter for id property.
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}
<?php

namespace App\Entity;

use App\Repository\DigitalResourceTypeDescriptorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DigitalResourceTypeDescriptorRepository::class)]
class DigitalResourceTypeDescriptor extends Entity
{
    use DescriptorTypeTrait;

    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Digital Resource Type';
}

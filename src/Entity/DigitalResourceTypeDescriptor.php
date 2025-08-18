<?php

namespace App\Entity;

use App\Repository\DigitalResourceTypeDescriptorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DigitalResourceTypeDescriptorRepository::class)]
class DigitalResourceTypeDescriptor
{
    use EntityTrait;
    use EntityIdTrait;
    use EntityDateTimeTrait;

    use DescriptorTypeTrait;

    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Digital Resource Type';
}

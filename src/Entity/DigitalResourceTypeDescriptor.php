<?php

namespace App\Entity;

use App\Repository\DigitalResourceTypeDescriptorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DigitalResourceTypeDescriptorRepository::class)
 */
class DigitalResourceTypeDescriptor extends Entity
{
    use DescriptorTypeTrait;
}

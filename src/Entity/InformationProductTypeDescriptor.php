<?php

namespace App\Entity;

use App\Repository\InformationProductTypeDescriptorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InformationProductTypeDescriptorRepository::class)
 */
class InformationProductTypeDescriptor extends Entity
{
    use DescriptorTypeTrait;
}

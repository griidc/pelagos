<?php

namespace App\Entity;

use App\Repository\ProductTypeDescriptorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductTypeDescriptorRepository::class)
 */
class ProductTypeDescriptor extends Entity
{
    use DescriptorTypeTrait;
}

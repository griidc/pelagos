<?php

namespace App\Entity;

use App\Repository\ProductTypeDescriptorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductTypeDescriptorRepository::class)]
class ProductTypeDescriptor
{
    use EntityTrait;
    use EntityIdTrait;
    use EntityDateTimeTrait;

    use DescriptorTypeTrait;

    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'Product Type';
}

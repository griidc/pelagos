<?php

namespace App\Entity;

use App\Repository\InformationProductTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InformationProductTypeRepository::class)
 */
class InformationProductType extends Entity
{
    use DescriptorTypeTrait;
}

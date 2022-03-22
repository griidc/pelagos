<?php

namespace App\Entity;

use App\Repository\ProductTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductTypeRepository::class)
 */
class ProductType
{
    use DescriptorTypeTrait;

    /**
     * @ORM\ManyToOne(targetEntity=InformationProduct::class, inversedBy="productType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $informationProduct;

    public function __construct(string $description)
    {
        $this->setDescription($description);
    }

    public function getInformationProduct(): ?InformationProduct
    {
        return $this->informationProduct;
    }

    public function setInformationProduct(?InformationProduct $informationProduct): self
    {
        $this->informationProduct = $informationProduct;

        return $this;
    }
}

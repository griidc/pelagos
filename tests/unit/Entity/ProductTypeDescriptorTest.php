<?php

namespace App\Tests\Entity;

use App\Entity\ProductTypeDescriptor;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\ProductTypeDescriptor.
 */
class ProductTypeDescriptorTest extends TestCase
{
    /**
    * Holds an instance of the Product Type Descriptor class for testing.
    */
    private $productTypeDescriptor;

    public function setUp(): void
    {
        $this->productTypeDescriptor = new ProductTypeDescriptor;
    }

    /**
     * Tests the Description.
     *
     * @return void
     */
    public function testDescription()
    {
        $testDescription = 'Hello Description!';
        $this->productTypeDescriptor->setDescription($testDescription);
        $this->assertSame($testDescription, $this->productTypeDescriptor->getDescription());
    }
}

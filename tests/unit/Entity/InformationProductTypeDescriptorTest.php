<?php

namespace App\Tests\Entity;

use App\Entity\InformationProductTypeDescriptor;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\InformationProductTypeDescriptor.
 */
class InformationProductTypeDescriptorTest extends TestCase
{
    /**
    * Holds an instance of the Product Type Descriptor class for testing.
    */
    private $informationProductTypeDescriptor;

    public function setUp()
    {
        $this->informationProductTypeDescriptor = new InformationProductTypeDescriptor;
    }

    /**
     * Tests the Description.
     *
     * @return void
     */
    public function testDescription()
    {
        $testDescription = 'Hello Description!';
        $this->informationProductTypeDescriptor->setDescription($testDescription);
        $this->assertSame($testDescription, $this->informationProductTypeDescriptor->getDescription());
    }
}

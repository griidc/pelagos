<?php

namespace App\Tests\Entity;

use App\Entity\DigitalResourceTypeDescriptor;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\DigitalResourceTypeDescriptor.
 */
class DigitalResourceTypeDescriptorTest extends TestCase
{
    /**
    * Holds an instance of the Digital Resource Type Descriptor class for testing.
    */
    private $digitalResourceTypeDescriptor;

    public function setUp(): void
    {
        $this->digitalResourceTypeDescriptor = new DigitalResourceTypeDescriptor;
    }

    /**
     * Tests the Description.
     *
     * @return void
     */
    public function testDescription()
    {
        $testDescription = 'Hello Description!';
        $this->digitalResourceTypeDescriptor->setDescription($testDescription);
        $this->assertSame($testDescription, $this->digitalResourceTypeDescriptor->getDescription());
    }
}

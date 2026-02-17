<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Udi;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\Udi.
 */
class UdiTest extends TestCase
{
    /**
     * The UDI instance to be tested.
     */
    private Udi $udi;

    /**
     * Test UDI string.
     */
    private const TEST_UDI = 'R1.x123.456.7890';

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        $this->udi = new Udi(self::TEST_UDI);
    }

    /**
     * Test the __toString method.
     */
    public function testToString(): void
    {
        $this->assertEquals(self::TEST_UDI, (string) $this->udi);
    }
}

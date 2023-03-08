<?php

namespace App\Tests\Entity;

use App\Entity\Funder;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\Funder.
 */
class FunderTest extends TestCase
{
    private Funder $funder;

    protected function setUp(): void
    {
        $this->funder = new Funder();
    }

    /**
     * Test Funder Name.
     *
     * @return void
     */
    public function testCanSetAndGetName()
    {
        // Set Get Name
        $this->funder->setName('Funder');
        $this->assertEquals('Funder', $this->funder->getName());
    }

    /**
     * Test to string of Funder.
     */
    public function testFunderToString(): void
    {
        // To String Test
        $this->funder->setName('Funder');
        $this->assertEquals('Funder', $this->funder);
    }

    /**
     * Test Funder Reference URI.
     */
    public function testCanSetAndGetReferenceId(): void
    {
        // Set Get Reference URI
        $this->funder->setReferenceUri('http://bla.com');
        $this->assertEquals('http://bla.com', $this->funder->getReferenceUri());
    }

    /**
     * Test Funder Source.
     */
    public function testCanSetAndGetSource(): void
    {
        // Set Get Reference URI
        $this->funder->setSource(Funder::SOURCE_DRPM);
        $this->assertEquals(Funder::SOURCE_DRPM, $this->funder->getSource());

        $this->expectException(\InvalidArgumentException::class);
        $this->funder->setSource('SOMEWHERE');
    }
}

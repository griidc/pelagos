<?php

namespace Pelagos\Entity;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Pelagos\Entity\DOI.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\DOI
 *
 * @package Pelagos\Entity
 */
class DoiTest extends TestCase
{
    /**
     * Dataset undergoing test.
     *
     * @var Dataset
     */
    protected $doi;

    /**
     * Test DateTime
     *
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of Doi and sets (some of) its properties.
     *
     * @return void
     */
    public function setUp()
    {
        $this->dateTime = new \DateTime();
        $this->doi = new DOI('doi:10.1234/TEST');
        $this->doi->setPublicDate($this->dateTime);
    }

    /**
     * Test getDoi.
     *
     * @return void
     */
    public function testGetDoi()
    {
        $this->assertEquals(
            '10.1234/TEST',
            $this->doi->getDoi()
        );
    }

    /**
     * Test getStatus.
     *
     * @return void
     */
    public function testGetStatus()
    {
        $this->assertEquals(
            DOI::STATUS_RESERVED,
            $this->doi->getStatus()
        );
    }

    /**
     * Test getPublicDate.
     *
     * @return void
     */
    public function testGetPublicDate()
    {
        $this->assertEquals(
            $this->dateTime,
            $this->doi->getPublicDate()
        );
    }
}

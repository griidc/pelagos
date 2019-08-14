<?php

namespace Pelagos\Entity;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Pelagos\Entity\Fileset.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\Fileset
 *
 * @package Pelagos\Entity
 */
class FilesetTest extends TestCase
{
    /**
     * Instance of Fileset.
     *
     * @var Fileset
     */
    protected $fileSet;

    /**
     * Setup for PHPUnit tests for this class.
     *
     * @return void
     */
    public function setUp() : void
    {
        $this->fileSet = new Fileset();
    }

    /**
     * Test setter and getter for attribute totalFileSize.
     *
     * @return void
     */
    public function testCanSetAndGetTotalFileSize() : void
    {
        $totalFileSize = 2343254;
        $this->fileSet->setTotalFileSize($totalFileSize);
        $this->assertEquals($totalFileSize, $this->fileSet->getTotalFileSize());
    }

    /**
     * Test setter and getter for attribute NumberOfFiles.
     *
     * @return void
     */
    public function testCanSetAndGetNumberOfFiles(): void
    {
        $noOfFiles = 100;
        $this->fileSet->setNumberOfFiles($noOfFiles);
        $this->assertEquals($noOfFiles, $this->fileSet->getNumberOfFiles());
    }
}

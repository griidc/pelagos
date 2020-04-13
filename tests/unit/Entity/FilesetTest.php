<?php

namespace App\Tests\unit\Entity;

use App\Entity\File;
use App\Entity\Fileset;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class FilesetTest extends TestCase
{

    /**
     * Instance of Fileset Entity.
     *
     * @var Fileset
     */
    protected $fileset;

    /**
     * A mock File.
     *
     * @var File
     */
    protected $mockFile;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of Fileset.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->fileset = new Fileset;
        $this->mockFile = \Mockery::mock(File::class, array('setFileset' => null));
    }

//    /**
//     * Test Adding files to fileset.
//     *
//     * @return void
//     */
//    public function testAddFileToFileset()
//    {
//        $this->fileset->addFile($this->mockFile);
//        $this->assertSame($this->mockFile, $this->fileset->getFiles()->first());
//    }
//
//    public function testRemoveFileToFileset()
//    {
//        $this->fileset->addFile($this->mockFile);
//        $this->assertSame(1, $this->fileset->getFiles()->count());;
//        $this->fileset->removeFile($this->mockFile);
//        $this->assertSame(0, $this->fileset->getFiles()->count());;
//    }
//
//    /**
//     * Test instance of files collection.
//     *
//     * @return void
//     */
//    public function testFilesCollection()
//    {
//        $this->fileset->addFile($this->mockFile);
//        $this->assertInstanceOf(Collection::class, $this->fileset->getFiles());
//    }
}
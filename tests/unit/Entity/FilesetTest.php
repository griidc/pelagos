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
     * The default File.
     *
     * @var File
     */
    protected $defaultFile;

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
        $this->mockFile = \Mockery::mock(
            File::class,
            array(
                'setFileset' => null,
                'getStatus' => 'new'
                ));
        $this->defaultFile = $this->fileset->getFiles()->first();
    }

    /**
     * Test Adding files to fileset.
     *
     * @return void
     */
    public function testAddFileToFileset()
    {
        if (!$this->fileset->getFiles()->isEmpty()) {
            $this->fileset->removeFile($this->defaultFile);
        }
        $this->fileset->addFile($this->mockFile);
        $this->assertEquals($this->mockFile, $this->fileset->getFiles()->first());
    }

    public function testRemoveFileToFileset()
    {
        $this->fileset->removeFile($this->mockFile);
        $this->assertSame(0, $this->fileset->getFiles()->count());
    }

    /**
     * Test instance of files collection.
     *
     * @return void
     */
    public function testFilesCollection()
    {
        $this->assertInstanceOf(Collection::class, $this->fileset->getFiles());
    }

    /**
     * Testing isDone function.
     *
     * @return void
     */
    public function testIsDone()
    {
        $newFile = \Mockery::mock(
            File::class,
            array(
                'setFileset' => null,
                'getStatus' => File::FILE_NEW
            )
        );

        $doneFile = \Mockery::mock(
            File::class,
            array(
                'setFileset' => null,
                'getStatus' => File::FILE_DONE
            )
        );

        $progressFile = \Mockery::mock(
            File::class,
            array(
                'setFileset' => null,
                'getStatus' => File::FILE_IN_PROGRESS
            )
        );

        $this->fileset->addFile($newFile);
        $this->assertSame(false, $this->fileset->isDone());
        $this->fileset->addFile($progressFile);
        $this->assertSame(false, $this->fileset->isDone());
        $this->fileset->removeFile($newFile);
        $this->assertSame(false, $this->fileset->isDone());
        $this->fileset->removeFile($progressFile);
        $this->fileset->addFile($doneFile);
        $this->assertSame(true, $this->fileset->isDone());
    }
}

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
                'getStatus' => File::FILE_NEW
                ));
        $this->defaultFile = $this->fileset->getAllFiles()->first();
    }

    /**
     * Test Adding files to fileset.
     *
     * @return void
     */
    public function testAddFileToFileset()
    {
        if (!$this->fileset->getAllFiles()->isEmpty()) {
            $this->fileset->removeFile($this->defaultFile);
        }
        $this->fileset->addFile($this->mockFile);
        $this->assertEquals($this->mockFile, $this->fileset->getAllFiles()->first());
    }

    /**
     * Test removing files from fileset.
     *
     * @return void
     */
    public function testRemoveFileToFileset()
    {
        $this->fileset->removeFile($this->mockFile);
        $this->assertSame(0, $this->fileset->getAllFiles()->count());;
    }

    /**
     * Test instance of files collection.
     *
     * @return void
     */
    public function testFilesCollection()
    {
        $this->assertInstanceOf(Collection::class, $this->fileset->getAllFiles());
    }

    /**
     * Test getter for processed files.
     *
     * @return void
     */
    public function testGetProcessedFilesCollection()
    {
        $mockProcessedFile = \Mockery::mock(
        File::class,
        array(
            'setFileset' => null,
            'getStatus' => File::FILE_DONE
        ));

        $this->fileset->addFile($mockProcessedFile);
        $this->assertSame($mockProcessedFile, $this->fileset->getProcessedFiles()->first());
    }

    /**
     * Test getter for new files.
     *
     * @return void
     */
    public function testGetNewFilesCollection()
    {
        $mockNewFile = \Mockery::mock(
            File::class,
            array(
                'setFileset' => null,
                'getStatus' => File::FILE_NEW
            ));

        $this->fileset->addFile($mockNewFile);
        $this->assertSame($mockNewFile, $this->fileset->getNewFiles()->first());
    }

    /**
     * Test getter for deleted files.
     *
     * @return void
     */
    public function testGetDeletedFilesCollection()
    {
        $mockDeletedFile = \Mockery::mock(
            File::class,
            array(
                'setFileset' => null,
                'getStatus' => File::FILE_DELETED
            ));

        $this->fileset->addFile($mockDeletedFile);
        $this->assertSame($mockDeletedFile, $this->fileset->getDeletedFiles()->first());
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

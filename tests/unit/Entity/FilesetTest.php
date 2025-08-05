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
    protected function setUp(): void
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
     * Testing getFileSize function.
     *
     * @return void
     */
    public function testFileSize()
    {
        $file1 = new File();
        $file1->setFileSize(100);
        $file1->setStatus(File::FILE_DONE);

        $file2  = new File();
        $file2->setFileSize(200);
        $file2->setStatus(File::FILE_DONE);

        $this->fileset->addFile($file1);
        $this->fileset->addFile($file2);

        $this->assertSame(300, $this->fileset->getFileSize());

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

        $this->fileset->addFile($newFile);
        $this->assertSame(false, $this->fileset->isDone());
        $this->fileset->removeFile($newFile);
        $this->assertSame(true, $this->fileset->isDone());
        $this->fileset->addFile($doneFile);
        $this->assertSame(true, $this->fileset->isDone());
    }

    /**
     * Test for zipFilePath setter and getter.
     *
     * @return void
     */
    public function testCanGetAdSetZipFilePath()
    {
        $zipFilePath = '/path/to/file.zip';
        $this->fileset->setZipFilePath($zipFilePath);
        $this->assertEquals($zipFilePath, $this->fileset->getZipFilePath());
    }

    /**
     * Test for zipFileSize setter and getter.
     *
     * @return void
     */
    public function testCanGetAdSetZipFileSize()
    {
        $zipFileSize = '237849';
        $this->fileset->setZipFileSize($zipFileSize);
        $this->assertEquals($zipFileSize, $this->fileset->getZipFileSize());
    }

    /**
     * Test for zipFileSha256Hash setter and getter.
     *
     * @return void
     */
    public function testCanGetAdSetZipFileSha256Hash()
    {
        $zipFileSha256Hash = 'cafesdfds';
        $this->fileset->setZipFileSha256Hash($zipFileSha256Hash);
        $this->assertEquals($zipFileSha256Hash, $this->fileset->getZipFileSha256Hash());
    }
}

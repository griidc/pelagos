<?php

namespace App\Tests\unit\Entity;

use App\Entity\File;
use App\Entity\Fileset;
use App\Entity\Person;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /**
     * Property to hold an instance of Fileset for testing.
     *
     * @var File
     */
    protected $file;

    /**
     * @var Fileset
     */
    protected $mockFileset;

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of File.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->mockFileset = \Mockery::mock('\App\Entity\Fileset');
        $this->file = new File;
    }

    /**
     * Test the Fileset setter and getter method.
     *
     * This method should return the Fileset.
     *
     * @return void
     */
    public function testCanSetAndGetFileset()
    {
        $this->file->setFileset($this->mockFileset);
        $this->assertSame(
            $this->mockFileset,
            $this->file->getFileset()
        );
    }

    /**
     * Test the Filename setter and getter method.
     *
     * This method should return the FileName.
     *
     * @return void
     */
    public function testCanSetAndGetFilePathName()
    {
        $mockFileName = 'testfile.txt';
        $this->file->setFilePathName($mockFileName);
        $this->assertEquals(
            $mockFileName,
            $this->file->getFilePathName()
        );
    }

    /**
     * Test the Filesize setter and getter method.
     *
     * This method should test the File size.
     *
     * @return void
     */
    public function testCanSetAndGetFileSize()
    {
        $mockFileSize = 9999999;
        $this->file->setFileSize($mockFileSize);
        $this->assertEquals(
            $mockFileSize,
            $this->file->getFileSize()
        );
    }

    /**
     * Test the Filesha256 setter and getter method.
     *
     * This method should return the Filesha256.
     *
     * @return void
     */
    public function testCanSetAndGetFileSha256Hash()
    {
        $mockFileSha256Hash = 'abcdef123123';
        $this->file->setFileSha256Hash($mockFileSha256Hash);
        $this->assertEquals(
            $mockFileSha256Hash,
            $this->file->getFileSha256Hash()
        );
    }

    /**
     * Test the File Description setter and getter method.
     *
     * This method should return the File Description.
     *
     * @return void
     */
    public function testCanSetAndGetDescription()
    {
        $mockFileDescription = 'sample test file';
        $this->file->setDescription($mockFileDescription);
        $this->assertEquals(
            $mockFileDescription,
            $this->file->getDescription()
        );
    }

    /**
     * Test the File Path setter and getter method.
     *
     * This method should return the File Path.
     *
     * @return void
     */
    public function testCanSetAndGetPhysicalFilePath()
    {
        $mockFilePath = '/path/to/file';
        $this->file->setPhysicalFilePath($mockFilePath);
        $this->assertEquals(
            $mockFilePath,
            $this->file->getPhysicalFilePath()
        );
    }

    /**
     * Test the file uploaded at setter and getter method.
     *
     * This method should return the File UploadedAt.
     *
     * @return void
     */
    public function testCanSetAndGetUploadedAt()
    {
        $mockFileUploadedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->file->setUploadedAt($mockFileUploadedAt);
        $this->assertEquals(
            $mockFileUploadedAt,
            $this->file->getUploadedAt()
        );
    }

    /**
     * Test the file uploaded by setter and getter method.
     *
     * This method should return the File UploadedBy.
     *
     * @return void
     */
    public function testCanSetAndGetUploadedBy()
    {
        $mockFileUploadedBy = new Person();
        $this->file->setUploadedBy($mockFileUploadedBy);
        $this->assertEquals(
            $mockFileUploadedBy,
            $this->file->getUploadedBy()
        );
    }

    /**
     * Test the file extension getter.
     *
     * This method should return the Extension of the file.
     *
     * @return void
     */
    public function testCanGetFileExtension()
    {
        $mockFileName = 'testfile.ext';
        $this->file->setFilePathName($mockFileName);
        $this->assertEquals(
            'ext',
            $this->file->getFileExtension()
        );
    }
}

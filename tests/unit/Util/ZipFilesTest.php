<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;

use Doctrine\Common\Collections\ArrayCollection;

use App\Entity\File;
use App\Entity\Fileset;
use App\Util\ZipFiles;

/**
 * Unit tests for App\Util\ZipFiles.
 */
class ZipFilesTest extends TestCase
{
    /**
     * The directory that contains the test data.
     *
     * @var string
     */
    protected $testDataDir = __DIR__ . '/../../_data/';
    
     /**
     * Unit test setup.
     *
     * @return void
     */
    public function setUp()
    {
        // Do nothing?
    }
    
    /**
     * Tests the createZipFile method.
     *
     * @return void
     */
    public function testCreateZipFile()
    {
        $fileset = new Fileset();
        
        $file1 = new File();
        $file1->setFilePath($this->testDataDir . 'aa.txt');
        $file1->setFileName('aa.txt');
        
        $file2 = new File();
        $file2->setFilePath($this->testDataDir . 'zz.txt');
        $file2->setFileName('zz.txt');
        
        $fileset->addFile($file1);
        $fileset->addFile($file2);
        
        $zipFiles = new ZipFiles();
        
        $zipFiles->createZipFile($fileset->getAllFiles(), '/tmp/testzip.zip');
        
        $this->assertFileEquals('/tmp/testzip.zip', $this->testDataDir . 'testzip.zip');
        
        unlink('/tmp/testzip.zip');
    }
}

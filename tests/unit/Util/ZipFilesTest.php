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
        
        $zip = new \ZipArchive;
        if ($zip->open('/tmp/testzip.zip') === TRUE) {
            $zip->extractTo('/tmp/');
            $zip->close();
        } else {
            throw new Exception('no worky');
        }
        
        $this->assertFileEquals('/tmp/aa.txt', $this->testDataDir . 'aa.txt');
        $this->assertFileEquals('/tmp/zz.txt', $this->testDataDir . 'zz.txt');
        
        unlink('/tmp/testzip.zip');
        unlink('/tmp/aa.txt');
        unlink('/tmp/zz.txt');
    }
}

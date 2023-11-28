<?php

namespace App\Tests\Util;

use App\Util\ZipFiles;
use GuzzleHttp\Psr7\Stream;
use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Util\ZipFiles.php.
 */
class ZipFilesTest extends TestCase
{
    /**
     * Test adding large file.
     */
    public function testAddFileToZip()
    {
        $zipFiles = new ZipFiles();

        $root = vfsStream::setup();
        $zipFile = vfsStream::newFile('test.zip')
            ->at($root);
        $resource = fopen($zipFile->url(), 'w+');

        $outputStream = new Stream($resource);
        $zipFiles->start($outputStream, 'test.zip');

        $largeFile = vfsStream::newFile('large.txt')
            ->withContent(LargeFileContent::withMegaBytes(10))
            ->at($root);

        $largeFileResource = fopen($largeFile->url(), 'r');
        $largeFileStream = new Stream($largeFileResource);
        $zipFiles->addFile('large.txt', $largeFileStream);

        $testFile = vfsStream::newFile('test.txt')
            ->withContent('test')
            ->at($root);

        $testFileResource = fopen($testFile->url(), 'r');
        $testFileStream = new Stream($testFileResource);
        $zipFiles->addFile('test.txt', $testFileStream);

        $zipFiles->finish();

        $this->assertEquals(10486068, $outputStream->getSize());
    }
}

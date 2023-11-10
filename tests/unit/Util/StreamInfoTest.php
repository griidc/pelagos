<?php

namespace App\Tests\Util;

use App\Util\StreamInfo;
use GuzzleHttp\Psr7\Utils as GuzzlePsr7Utils;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use Psr\Http\Message\StreamInterface;

/**
 * Unit tests for App\Util\StreamInfo.php
 */
class StreamInfoTest extends TestCase
{
    /**
     * The file stream.
     */
    private StreamInterface $fileStream;


    /**
     * Unit test setup.
     *
     * @return void
     */
    public function setUp()
    {
        $file = vfsStream::newFile('test.txt')
                ->withContent('test')
                ->at(vfsStream::setup());
        $this->fileStream = GuzzlePsr7Utils::streamFor(fopen($file->url(), 'r'));
    }

    /**
     * Test calculating the file size.
     */
    public function testFileSize()
    {
        $size = StreamInfo::getFileSize($this->fileStream);
        $this->assertEquals($size, 4);
    }

    /**
     * Test calculating the hash sha256.
     */
    public function testCalculateHashSha256()
    {
        $hash = StreamInfo::calculateHash($this->fileStream);
        $this->assertEquals($hash, '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08');
    }

    /**
     * Test calculating the hash md5.
     */
    public function testCalculateHashMd5()
    {
        $hash = StreamInfo::calculateHash($this->fileStream, 'md5');
        $this->assertEquals($hash, '098f6bcd4621d373cade4e832627b4f6');
    }
}

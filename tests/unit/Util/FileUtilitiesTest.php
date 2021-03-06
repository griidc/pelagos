<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;

use App\Util\FileNameUtilities;

/**
 * Unit tests for App\Util\StreamInfo.php
 */
class FileUtilitiesTest extends TestCase
{
    /**
     * Test calculating the file size.
     */
    public function testfixFileName()
    {
        $fakeFileName = "hello/world/test.txt";
        $fileName = FileNameUtilities::fixFileNameLength($fakeFileName);
        $this->assertLessThanOrEqual(FileNameUtilities::MAX_FILE_NAME_LENGTH, strlen(basename($fileName)));
    }

    /**
     * Test calculating the file size.
     */
    public function testfixFileNameWithSetLength()
    {
        $fakeFileName = "file/path/" . $this->generateRandomString(64) . ".txt";
        $fileName = FileNameUtilities::fixFileNameLength($fakeFileName,16);
        $this->assertLessThanOrEqual(16, strlen(basename($fileName)));
    }

    /**
     * Test calculating the file size.
     */
    public function testfixLongFileName()
    {
        $fakeFileName = "file/path/" . $this->generateRandomString() . ".txt";
        $fileName = FileNameUtilities::fixFileNameLength($fakeFileName);
        $this->assertLessThanOrEqual(FileNameUtilities::MAX_FILE_NAME_LENGTH, strlen(basename($fileName)));
    }

    /**
     * This generates a random string of characters.
     */
    private function generateRandomString($length = FileNameUtilities::MAX_FILE_NAME_LENGTH) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

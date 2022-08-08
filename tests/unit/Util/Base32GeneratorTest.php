<?php

namespace App\Tests\Util;

use PHPUnit\Framework\TestCase;
use App\Util\Base32Generator;

/**
 * Unit tests for App\Util\Base32Generator.php
 */
class Base32GeneratorTest extends TestCase
{
    /**
     * Test generation of an ID.
     */
    public function testGenerateId()
    {
        $this->assertMatchesRegularExpression(
            '/[0123456789abcdefghijklmnopqrstuvwxyz]{' . Base32Generator::DEFAULTLENGTH . '}/',
            Base32Generator::generateId()
        );
    }
}

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
            '/['. Base32Generator::CROCKFORDALPHABET .']{' . Base32Generator::DEFAULTLENGTH . '}/',
            Base32Generator::generateId()
        );
    }
}

<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Keyword;
use App\Enum\KeywordType;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\Keyword.
 */
class KeywordTest extends TestCase
{
    /**
     * The keyword instance to be tested.
     */
    private Keyword $keyword;

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        $this->keyword = new Keyword();
    }

    /**
     * Test the getType and setType methods.
     */
    public function testGetAndSetType(): void
    {
        $this->keyword->setType(KeywordType::TYPE_GCMD);
        $this->assertEquals(KeywordType::TYPE_GCMD, $this->keyword->getType());
    }

    /**
     * Test the getIdentifier and setIdentifier methods.
     */
    public function testGetAndSetIdentifier(): void
    {
        $this->keyword->setIdentifier('test-identifier');
        $this->assertEquals('test-identifier', $this->keyword->getIdentifier());
    }

    /**
     * Test the getDefinition and setDefinition methods.
     */
    public function testGetAndSetDefinition(): void
    {
        $this->keyword->setDefinition('Test definition.');
        $this->assertEquals('Test definition.', $this->keyword->getDefinition());
    }

    /**
     * Test the getLabel and setLabel methods.
     */
    public function testGetAndSetLabel(): void
    {
        $this->keyword->setLabel('Test Label');
        $this->assertEquals('Test Label', $this->keyword->getLabel());
    }

    /**
     * Test the getReferenceUri and setReferenceUri methods.
     */
    public function testGetAndSetReferenceUri(): void
    {
        $this->keyword->setReferenceUri('http://test.com');
        $this->assertEquals('http://test.com', $this->keyword->getReferenceUri());
    }

    /**
     * Test the getParentUri and setParentUri methods.
     */
    public function testGetAndSetParentUri(): void
    {
        $this->keyword->setParentUri('http://parent.com');
        $this->assertEquals('http://parent.com', $this->keyword->getParentUri());
    }

    /**
     * Test the getDisplayPath and setDisplayPath methods.
     */
    public function testGetAndSetDisplayPath(): void
    {
        $this->keyword->setDisplayPath('Parent > Child');
        $this->assertEquals('Parent > Child', $this->keyword->getDisplayPath());
    }

    /**
     * Test the isExpanded and setExpanded methods.
     */
    public function testIsAndSetExpanded(): void
    {
        $this->keyword->setExpanded(true);
        $this->assertTrue($this->keyword->isExpanded());
        $this->keyword->setExpanded(false);
        $this->assertFalse($this->keyword->isExpanded());
    }

    /**
     * Test the hasItems method.
     */
    public function testHasItems(): void
    {
        $this->assertFalse($this->keyword->hasItems());
        $this->keyword->setParentUri('http://parent.com');
        $this->assertTrue($this->keyword->hasItems());
    }

    /**
     * Test the getShortDisplayPath method.
     */
    public function testGetShortDisplayPath(): void
    {
        $this->keyword->setDisplayPath('Science Keywords > Earth Science > Oceans');
        $this->assertEquals('Earth Science > Oceans', $this->keyword->getShortDisplayPath());
        $this->keyword->setDisplayPath('Another > Path');
        $this->assertEquals('Another > Path', $this->keyword->getShortDisplayPath());
    }

    /**
     * Test the getLevel method.
     */
    public function testGetLevel(): void
    {
        $this->keyword->setDisplayPath('Level 1 > Level 2 > Level 3');
        $this->assertEquals(3, $this->keyword->getLevel());

        $this->keyword->setType(KeywordType::TYPE_GCMD);
        $this->assertEquals(2, $this->keyword->getLevel());

        $this->assertEquals(3, $this->keyword->getLevel(false));
    }

    /**
     * Test the getLevelTwo method.
     */
    public function testGetLevelTwo(): void
    {
        $this->assertNull($this->keyword->getLevelTwo());
        $this->keyword->setDisplayPath('Level 1 > Level 2 > Level 3');
        $this->assertEquals('Level 1 > Level 2', $this->keyword->getLevelTwo());
    }

    /**
     * Test the __toString method.
     */
    public function testToString(): void
    {
        $this->keyword->setLabel('Test Label');
        $this->assertEquals('Test Label', (string) $this->keyword);
    }
}
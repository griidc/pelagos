<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Publication;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\Publication.
 */
class PublicationTest extends TestCase
{
    /**
     * The publication instance to be tested.
     */
    private Publication $publication;

    /**
     * Test DOI.
     */
    private const TEST_DOI = '10.1234/5678';

    /**
     * Test citation text with a year.
     */
    private const TEST_CITATION_WITH_YEAR = 'Author, A. (2023). Title of the publication. Journal Name, 1(2), 123-456.';

    /**
     * Test citation text without a year.
     */
    private const TEST_CITATION_WITHOUT_YEAR = 'Author, A. Title of the publication. Journal Name, 1(2), 123-456.';


    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        $this->publication = new Publication(self::TEST_DOI);
    }

    /**
     * Test the getDoi method.
     */
    public function testGetDoi(): void
    {
        $this->assertEquals(self::TEST_DOI, $this->publication->getDoi());
    }

    /**
     * Test the setCitationText and getCitationText methods.
     */
    public function testSetAndGetCitationText(): void
    {
        $this->publication->setCitationText(self::TEST_CITATION_WITH_YEAR);
        $this->assertEquals(self::TEST_CITATION_WITH_YEAR, $this->publication->getCitationText());
    }

    /**
     * Test the getPublicationYear method.
     */
    public function testGetPublicationYear(): void
    {
        $this->publication->setCitationText(self::TEST_CITATION_WITH_YEAR);
        $this->assertEquals('2023', $this->publication->getPublicationYear());
    }

    /**
     * Test the getPublicationYear method when no year is present.
     */
    public function testGetPublicationYearWithNoYear(): void
    {
        $this->publication->setCitationText(self::TEST_CITATION_WITHOUT_YEAR);
        $this->assertNull($this->publication->getPublicationYear());
    }

    /**
     * Test the __toString method.
     */
    public function testToString(): void
    {
        $this->assertEquals(self::TEST_DOI, (string) $this->publication);
    }
}

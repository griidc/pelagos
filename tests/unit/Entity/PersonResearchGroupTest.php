<?php

namespace App\Tests\Entity;

use App\Entity\PersonResearchGroup;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Error;

/**
 * Unit tests for App\Entity\PersonResearchGroup.
 */
class PersonResearchGroupTest extends TestCase
{
    /**
     * Property to hold an instance of PersonResearchGroup for testing.
     *
     * @var PersonResearchGroup $personResearchGroup
     */
    protected $personResearchGroup;

    /**
     * Static class variable containing a label to use for testing.
     *
     * @var string $testLabel
     */
    protected static $testLabel = 'Dictator';

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of PersonResearchGroup.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->mockPerson = \Mockery::mock('\App\Entity\Person');
        $this->mockResearchGroup = \Mockery::mock('\App\Entity\ResearchGroup');
        $this->mockResearchGroupRole = \Mockery::mock('\App\Entity\ResearchGroupRole');
        $this->personResearchGroup = new PersonResearchGroup;
        $this->personResearchGroup->setPerson($this->mockPerson);
        $this->personResearchGroup->setResearchGroup($this->mockResearchGroup);
        $this->personResearchGroup->setRole($this->mockResearchGroupRole);
        $this->personResearchGroup->setLabel(self::$testLabel);
    }

    /**
     * Test the getPerson method.
     *
     * This method should return the Person set in setUp.
     *
     * @return void
     */
    public function testGetPerson()
    {
        $this->assertSame(
            $this->mockPerson,
            $this->personResearchGroup->getPerson()
        );
    }

    /**
     * Test the setPerson method with null.
     *
     * This should be allowed and null should be returned by getPerson.
     *
     * @return void
     */
    public function testSetPersonNull()
    {
        $this->personResearchGroup->setPerson(null);
        $this->assertNull(
            $this->personResearchGroup->getPerson()
        );
    }

    /**
     * Test the getResearchGroup method.
     *
     * This method should return the ResearchGroup set in setUp.
     *
     * @return void
     */
    public function testGetResearchGroup()
    {
        $this->assertSame(
            $this->mockResearchGroup,
            $this->personResearchGroup->getResearchGroup()
        );
    }

    /**
     * Test the setResearchGroup method with null.
     *
     * This should be allowed and null should be returned by getResearchGroup.
     *
     * @return void
     */
    public function testSetResearchGroupNull()
    {
        $this->personResearchGroup->setResearchGroup(null);
        $this->assertNull(
            $this->personResearchGroup->getResearchGroup()
        );
    }

    /**
     * Test the getRole method.
     *
     * This method should return the ResearchGroupRole set in setUp.
     *
     * @return void
     */
    public function testGetRole()
    {
        $this->assertSame(
            $this->mockResearchGroupRole,
            $this->personResearchGroup->getRole()
        );
    }

    /**
     * Test the setRole method with null.
     *
     * This should be allowed and null should be returned by getRole.
     *
     * @return void
     */
    public function testSetRoleNull()
    {
        $this->personResearchGroup->setRole(null);
        $this->assertNull(
            $this->personResearchGroup->getRole()
        );
    }

    /**
     * Test the getLabel method.
     *
     * This method should return the label that was set in setUp.
     *
     * @return void
     */
    public function testGetLabel()
    {
        $this->assertEquals(
            self::$testLabel,
            $this->personResearchGroup->getLabel()
        );
    }

    /**
     * Test setLabel with null.
     *
     * This should be allowed and null should be returned by getLabel.
     *
     * @return void
     */
    public function testSetLabelNull()
    {
        $this->personResearchGroup->setLabel(null);
        $this->assertNull(
            $this->personResearchGroup->getLabel()
        );
    }
}

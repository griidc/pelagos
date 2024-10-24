<?php

namespace App\Tests\Entity;

use App\Entity\Entity;
use App\Entity\Person;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

/**
 * Unit tests for App\Entity\Entity.
 */
class EntityTest extends TestCase
{
    /**
     * Property to hold an instance of a mock Entity for testing.
     *
     * @var Entity
     */
    protected $testEntity;

    /**
     * Property to hold an instance of the Symfony Validator.
     *
     * @var \Symfony\Component\Validator\Validator
     */
    protected $validator;

    /**
     * Property to hold a time stamp to use in testing.
     *
     * @var \DateTime
     */
    protected $timeStamp;

    /**
     * Property to hold an ISO 8601 representation of a time stamp to use in testing.
     *
     * @var string
     */
    protected $timeStampISO;

    /**
     * Property to hold a localized time stamp to use in testing.
     *
     * @var \DateTime
     */
    protected $timeStampLocalized;

    /**
     * Property to hold an ISO 8601 representation of a localized time stamp to use in testing.
     *
     * @var string
     */
    protected $timeStampLocalizedISO;

    /**
     * Property to hold a creator to use for testing.
     *
     * @var Person
     */
    protected $testCreator;

    /**
     * Setup for PHPUnit tests.
     *
     * This includes the autoloader and instantiates an instance of FundingCycle.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->testCreator = new Person();
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping(true)
            ->getValidator();
        $this->testEntity = $this
            ->getMockBuilder('App\Entity\Entity')
            ->getMockForAbstractClass();
        $this->testEntity->setCreator($this->testCreator);
        $this->testEntity->setModifier($this->testCreator);
        $this->timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->timeStampISO = $this->timeStamp->format(\DateTime::ISO8601);
        $this->timeStampLocalized = clone $this->timeStamp;
        $this->timeStampLocalized->setTimeZone(new \DateTimeZone(date_default_timezone_get()));
        $this->timeStampLocalizedISO = $this->timeStampLocalized->format(\DateTime::ISO8601);
    }

    /**
     * Test the getId method.
     *
     * This method should always return null because it can not be set (even by the constructor).
     * The id property can only be set when a FundingCycle is instantiated from persistence by Doctrine.
     *
     * @return void
     */
    public function testGetID()
    {
        $this->assertEquals(
            $this->testEntity->getId(),
            null
        );
    }

    /**
     * Test the getCreator method.
     *
     * This method should return the creator that was set in setUp.
     *
     * @return void
     */
    public function testGetCreator()
    {
        $this->assertSame(
            $this->testEntity->getCreator(),
            $this->testCreator
        );
    }

    /**
     * Test the getModifier method.
     *
     * This method should return the modifier that was set in setUp by setCreator (which also sets the modifier).
     *
     * @return void
     */
    public function testGetModifier()
    {
        $this->assertEquals(
            $this->testEntity->getModifier(),
            $this->testCreator
        );
    }

    /**
     * Test the setCreationTimeStamp method.
     *
     * This method should accept a \DateTime object in UTC.
     * We should be able to get back the same timestamp in UTC
     * if we call getCreationTimeStamp(false) (non-localized).
     *
     * @return void
     */
    public function testSetCreationTimeStamp()
    {
        $timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $timeStampISO = $timeStamp->format(\DateTime::ISO8601);
        $this->testEntity->setCreationTimeStamp($timeStamp);
        $creationTimeStamp = $this->testEntity->getCreationTimeStamp(false);
        $this->assertInstanceOf('\DateTime', $creationTimeStamp);
        $this->assertEquals($timeStampISO, $creationTimeStamp->format(\DateTime::ISO8601));
    }

    /**
     * Test the setCreationTimeStamp method with a non-UTC timestamp.
     *
     * @return void
     */
    public function testSetCreationTimeStampFailForNonUTC()
    {
        $this->expectException(\Exception::class);
        $this->testEntity->setCreationTimeStamp(
            new \DateTime('now', new \DateTimeZone('America/Chicago'))
        );
    }

    /**
     * Test the getCreationTimeStamp method.
     *
     * This method should return a \DateTime object in UTC.
     *
     * @return void
     */
    public function testGetCreationTimeStamp()
    {
        $this->testEntity->setCreationTimeStamp($this->timeStamp);
        $creationTimeStamp = $this->testEntity->getCreationTimeStamp();
        $this->assertInstanceOf('\DateTime', $creationTimeStamp);
        $this->assertEquals(
            'UTC',
            $creationTimeStamp->getTimezone()->getName()
        );
        $this->assertEquals($this->timeStamp, $creationTimeStamp);
    }

    /**
     * Test the getCreationTimeStamp method (localized).
     *
     * This method should return a \DateTime object localized to the current timezone.
     *
     * @return void
     */
    public function testGetCreationTimeStampLocalized()
    {
        $this->testEntity->setCreationTimeStamp($this->timeStamp);
        $creationTimeStamp = $this->testEntity->getCreationTimeStamp(true);
        $this->assertInstanceOf('\DateTime', $creationTimeStamp);
        $this->assertEquals(
            date_default_timezone_get(),
            $creationTimeStamp->getTimezone()->getName()
        );
        $this->assertEquals($this->timeStamp, $creationTimeStamp);
    }

    /**
     * Test the getCreationTimeStampAsISO method.
     *
     * This method should return a string containing the ISO 8601 representation
     * of the creation time stamp localized to the current timezone.
     *
     * @return void
     */
    public function testGetCreationTimeStampAsISO()
    {
        $this->testEntity->setCreationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampISO,
            $this->testEntity->getCreationTimeStampAsISO()
        );
    }

    /**
     * Test the getCreationTimeStampAsISO method.
     *
     * This method should return a string containing the ISO 8601 representation
     * of the creation time stamp localized to the current timezone.
     *
     * @return void
     */
    public function testGetCreationTimeStampAsISOLocalized()
    {
        $this->testEntity->setCreationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampLocalizedISO,
            $this->testEntity->getCreationTimeStampAsISO(true)
        );
    }

    /**
     * Test the getCreationTimeStampAsISO method when creationTimeStamp is null.
     *
     * This method should return null in this case.
     *
     * @return void
     */
    public function testGetCreationTimeStampAsISONull()
    {
        $this->assertNull($this->testEntity->getCreationTimeStampAsISO());
    }

    /**
     * Test the setModificationTimeStamp method.
     *
     * This method should accept a \DateTime object in UTC.
     * We should be able to get back the same timestamp in UTC
     * if we call getModificationTimeStamp(false) (non-localized).
     *
     * @return void
     */
    public function testSetModificationTimeStamp()
    {
        $timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $timeStampISO = $timeStamp->format(\DateTime::ISO8601);
        $this->testEntity->setModificationTimeStamp($timeStamp);
        $modificationTimeStamp = $this->testEntity->getModificationTimeStamp(false);
        $this->assertInstanceOf('\DateTime', $modificationTimeStamp);
        $this->assertEquals($timeStampISO, $modificationTimeStamp->format(\DateTime::ISO8601));
    }

    /**
     * Test the setModificationTimeStamp method with a non-UTC timestamp.
     *
     * @return void
     */
    public function testSetModificationTimeStampFailForNonUTC()
    {
        $this->expectException(\Exception::class);
        $this->testEntity->setModificationTimeStamp(
            new \DateTime('now', new \DateTimeZone('America/Chicago'))
        );
    }

    /**
     * Test the getModificationTimeStamp method.
     *
     * This method should return a \DateTime object in UTC.
     *
     * @return void
     */
    public function testGetModificationTimeStamp()
    {
        $this->testEntity->setModificationTimeStamp($this->timeStamp);
        $modificationTimeStamp = $this->testEntity->getModificationTimeStamp();
        $this->assertInstanceOf('\DateTime', $modificationTimeStamp);
        $this->assertEquals(
            'UTC',
            $modificationTimeStamp->getTimezone()->getName()
        );
        $this->assertEquals($this->timeStamp, $modificationTimeStamp);
    }

    /**
     * Test the getModificationTimeStamp method (localized).
     *
     * This method should return a \DateTime object localized to the current timezone.
     *
     * @return void
     */
    public function testGetModificationTimeStampLocalized()
    {
        $this->testEntity->setModificationTimeStamp($this->timeStamp);
        $modificationTimeStamp = $this->testEntity->getModificationTimeStamp(true);
        $this->assertInstanceOf('\DateTime', $modificationTimeStamp);
        $this->assertEquals(
            date_default_timezone_get(),
            $modificationTimeStamp->getTimezone()->getName()
        );
        $this->assertEquals($this->timeStamp, $modificationTimeStamp);
    }

    /**
     * Test the getModificationTimeStampAsISO method.
     *
     * This method should return a string containing the ISO 8601 representation
     * of the creation time stamp localized to the current timezone.
     *
     * @return void
     */
    public function testGetModificationTimeStampAsISO()
    {
        $this->testEntity->setModificationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampISO,
            $this->testEntity->getModificationTimeStampAsISO()
        );
    }

    /**
     * Test the getModificationTimeStampAsISO method.
     *
     * This method should return a string containing the ISO 8601 representation
     * of the creation time stamp localized to the current timezone.
     *
     * @return void
     */
    public function testGetModificationTimeStampAsISOLocalized()
    {
        $this->testEntity->setModificationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampLocalizedISO,
            $this->testEntity->getModificationTimeStampAsISO(true)
        );
    }

    /**
     * Test the getModificationTimeStampAsISO method when creationTimeStamp is null.
     *
     * This method should return null in this case.
     *
     * @return void
     */
    public function testGetModificationTimeStampAsISONull()
    {
        $this->assertNull($this->testEntity->getModificationTimeStampAsISO());
    }

    /**
     * Test that updateTimeStamps sets both creationTimeStamp and modificationTimeStamp.
     *
     * @return void
     */
    public function testUpdateTimeStamps()
    {
        $this->assertNull($this->testEntity->getCreationTimeStamp());
        $this->assertNull($this->testEntity->getModificationTimeStamp());
        $this->testEntity->updateTimeStamps();
        $this->assertInstanceOf('\DateTime', $this->testEntity->getCreationTimeStamp());
        $this->assertInstanceOf('\DateTime', $this->testEntity->getModificationTimeStamp());
    }

//    /**
//     * Test that time stamps serialize to a specified time zone.
//     *
//     * @return void
//     */
//    public function testSerializeTimeStampsTimeZone()
//    {
//        $timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
//        $this->testEntity->setCreationTimeStamp($timeStamp);
//        $this->testEntity->setTimeZone('America/Chicago');
//        $timeStamp->setTimeZone(new \DateTimeZone('America/Chicago'));
//        $timeStampISO = $timeStamp->format(\DateTime::ISO8601);
//        $testEntityData = array(
//            'id' => null,
//            'creator' => $this->testCreator,
//            'creationTimeStamp' => $timeStampISO,
//            'modifier' => $this->testCreator,
//            'modificationTimeStamp' => $timeStampISO,
//            'name' => null,
//        );
//    }

    /**
     * Test that there is a checkDeletable method that is callable.
     *
     * @return void
     */
    public function testCheckDeletable()
    {
        $this->assertTrue(method_exists($this->testEntity, 'checkDeletable'));
        $this->testEntity->checkDeletable();
    }

    /**
     * Tests the array blank-filter method.
     *
     * @return void
     */
    public function testFilterOutBlanksFromArray()
    {
        // Filter empty string is filtered out - the typical use case.
        $this->assertEquals(['a', 'b', 'c'], $this->testEntity->filterArrayBlanks(['a', '', 'b', 'c']));
        // Ensure bool false is filtered out.
        $this->assertEquals(['a', 'b', 'c'], $this->testEntity->filterArrayBlanks(['a', false, 'b', 'c']));
        // Ensure a null value is filtered out.
        $this->assertEquals(['a', 'b', 'c'], $this->testEntity->filterArrayBlanks(['a', null, 'b', 'c']));
        // Ensure a boolean true is not filtered out.
        $this->assertNotEquals(['a', 'b', 'c'], $this->testEntity->filterArrayBlanks(['a', true, 'b', 'c']));
        // Ensure the value 0 (zero) is not filtered out.
        $this->assertNotEquals(['a', 'b', 'c'], $this->testEntity->filterArrayBlanks(['a', 0, 'b', 'c']));
    }
}

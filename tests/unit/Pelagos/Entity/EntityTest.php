<?php

namespace Pelagos\Entity;

use Symfony\Component\Validator\Validation;

/**
 * Unit tests for Pelagos\Entity\EntityTestClass.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\EntityTestClass
 */
class EntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instancet of EntityTestClass for testing.
     *
     * @var EntityTestClass $entitytestclass
     */
    protected $entitytestclass;

    /**
     * Property to hold an instance of the Symfony Validator.
     *
     * @var \Symfony\Component\Validator\Validator $validator
     */
    protected $validator;

    /**
     * Property to hold a time stamp to use in testing.
     *
     * @var \DateTime $timeStamp
     */
    protected $timeStamp;

    /**
     * Property to hold an ISO 8601 representation of a time stamp to use in testing.
     *
     * @var string $timeStampISO
     */
    protected $timeStampISO;

    /**
     * Property to hold a localized time stamp to use in testing.
     *
     * @var \DateTime $timeStampLocalized
     */
    protected $timeStampLocalized;

    /**
     * Property to hold an ISO 8601 representation of a localized time stamp to use in testing.
     *
     * @var string $timeStampLocalizedISO
     */
    protected $timeStampLocalizedISO;

    /**
     * Static class variable containing creator to use for testing.
     *
     * @var string $testCreator
     */
    protected static $testCreator = 'tuser';

    /**
     * Setup for PHPUnit tests.
     *
     * This includes the autoloader and instantiates an instance of FundingCycle.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
        $this->entitytestclass = new EntityTestClass;
        $this->entitytestclass->setCreator(self::$testCreator);
        $this->entitytestclass->setModifier(self::$testCreator);
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
            $this->entitytestclass->getId(),
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
        $this->assertEquals(
            $this->entitytestclass->getCreator(),
            self::$testCreator
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
            $this->entitytestclass->getModifier(),
            self::$testCreator
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
        $this->entitytestclass->setCreationTimeStamp($timeStamp);
        $creationTimeStamp = $this->entitytestclass->getCreationTimeStamp(false);
        $this->assertInstanceOf('\DateTime', $creationTimeStamp);
        $this->assertEquals($timeStampISO, $creationTimeStamp->format(\DateTime::ISO8601));
    }

    /**
     * Test the setCreationTimeStamp method with a non-UTC timestamp.
     *
     * @return void
     *
     * @expectedException \Exception
     */
    public function testSetCreationTimeStampFailForNonUTC()
    {
        $this->entitytestclass->setCreationTimeStamp(
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
        $this->entitytestclass->setCreationTimeStamp($this->timeStamp);
        $creationTimeStamp = $this->entitytestclass->getCreationTimeStamp();
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
        $this->entitytestclass->setCreationTimeStamp($this->timeStamp);
        $creationTimeStamp = $this->entitytestclass->getCreationTimeStamp(true);
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
        $this->entitytestclass->setCreationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampISO,
            $this->entitytestclass->getCreationTimeStampAsISO()
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
        $this->entitytestclass->setCreationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampLocalizedISO,
            $this->entitytestclass->getCreationTimeStampAsISO(true)
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
        $this->assertNull($this->entitytestclass->getCreationTimeStampAsISO());
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
        $this->entitytestclass->setModificationTimeStamp($timeStamp);
        $modificationTimeStamp = $this->entitytestclass->getModificationTimeStamp(false);
        $this->assertInstanceOf('\DateTime', $modificationTimeStamp);
        $this->assertEquals($timeStampISO, $modificationTimeStamp->format(\DateTime::ISO8601));
    }

    /**
     * Test the setModificationTimeStamp method with a non-UTC timestamp.
     *
     * @return void
     *
     * @expectedException \Exception
     */
    public function testSetModificationTimeStampFailForNonUTC()
    {
        $this->entitytestclass->setModificationTimeStamp(
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
        $this->entitytestclass->setModificationTimeStamp($this->timeStamp);
        $modificationTimeStamp = $this->entitytestclass->getModificationTimeStamp();
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
        $this->entitytestclass->setModificationTimeStamp($this->timeStamp);
        $modificationTimeStamp = $this->entitytestclass->getModificationTimeStamp(true);
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
        $this->entitytestclass->setModificationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampISO,
            $this->entitytestclass->getModificationTimeStampAsISO()
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
        $this->entitytestclass->setModificationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampLocalizedISO,
            $this->entitytestclass->getModificationTimeStampAsISO(true)
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
        $this->assertNull($this->entitytestclass->getModificationTimeStampAsISO());
    }

    /**
     * Test that updateTimeStamps sets both creationTimeStamp and modificationTimeStamp.
     *
     * @return void
     */
    public function testUpdateTimeStamps()
    {
        $this->assertNull($this->entitytestclass->getCreationTimeStamp());
        $this->assertNull($this->entitytestclass->getModificationTimeStamp());
        $this->entitytestclass->updateTimeStamps();
        $this->assertInstanceOf('\DateTime', $this->entitytestclass->getCreationTimeStamp());
        $this->assertInstanceOf('\DateTime', $this->entitytestclass->getModificationTimeStamp());
    }

    /**
     * Test that FundingCycle is JsonSerializable and serializes to the expected JSON.
     *
     * @return void
     */
    public function testJsonSerialize()
    {
        $timeStamp = new \DateTime('now', new \DateTimeZone('UTC'));
        $timeStampISO = $timeStamp->format(\DateTime::ISO8601);
        $entitytestclassData = array(
            'id' => null,
            'creationTimeStamp' => $timeStampISO,
            'creator' => self::$testCreator,
        );
        $this->entitytestclass->setCreationTimeStamp($timeStamp);
        $this->assertEquals(json_encode($entitytestclassData), json_encode($this->entitytestclass));
    }

}

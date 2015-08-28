<?php

namespace Pelagos\Entity;

use Symfony\Component\Validator\Validation;

/**
 * Unit tests for Pelagos\Entity\Entity.
 *
 * @group Pelagos
 * @group Pelagos\Entity
 * @group Pelagos\Entity\Entity
 */
class EntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Property to hold an instance of ConcreteEntity for testing.
     *
     * @var ConcreteEntity $concreteEntity
     */
    protected $concreteEntity;

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
        $this->concreteEntity = new ConcreteEntity;
        $this->concreteEntity->setCreator(self::$testCreator);
        $this->concreteEntity->setModifier(self::$testCreator);
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
            $this->concreteEntity->getId(),
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
            $this->concreteEntity->getCreator(),
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
            $this->concreteEntity->getModifier(),
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
        $this->concreteEntity->setCreationTimeStamp($timeStamp);
        $creationTimeStamp = $this->concreteEntity->getCreationTimeStamp(false);
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
        $this->concreteEntity->setCreationTimeStamp(
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
        $this->concreteEntity->setCreationTimeStamp($this->timeStamp);
        $creationTimeStamp = $this->concreteEntity->getCreationTimeStamp();
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
        $this->concreteEntity->setCreationTimeStamp($this->timeStamp);
        $creationTimeStamp = $this->concreteEntity->getCreationTimeStamp(true);
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
        $this->concreteEntity->setCreationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampISO,
            $this->concreteEntity->getCreationTimeStampAsISO()
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
        $this->concreteEntity->setCreationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampLocalizedISO,
            $this->concreteEntity->getCreationTimeStampAsISO(true)
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
        $this->assertNull($this->concreteEntity->getCreationTimeStampAsISO());
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
        $this->concreteEntity->setModificationTimeStamp($timeStamp);
        $modificationTimeStamp = $this->concreteEntity->getModificationTimeStamp(false);
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
        $this->concreteEntity->setModificationTimeStamp(
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
        $this->concreteEntity->setModificationTimeStamp($this->timeStamp);
        $modificationTimeStamp = $this->concreteEntity->getModificationTimeStamp();
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
        $this->concreteEntity->setModificationTimeStamp($this->timeStamp);
        $modificationTimeStamp = $this->concreteEntity->getModificationTimeStamp(true);
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
        $this->concreteEntity->setModificationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampISO,
            $this->concreteEntity->getModificationTimeStampAsISO()
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
        $this->concreteEntity->setModificationTimeStamp($this->timeStamp);
        $this->assertEquals(
            $this->timeStampLocalizedISO,
            $this->concreteEntity->getModificationTimeStampAsISO(true)
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
        $this->assertNull($this->concreteEntity->getModificationTimeStampAsISO());
    }

    /**
     * Test that updateTimeStamps sets both creationTimeStamp and modificationTimeStamp.
     *
     * @return void
     */
    public function testUpdateTimeStamps()
    {
        $this->assertNull($this->concreteEntity->getCreationTimeStamp());
        $this->assertNull($this->concreteEntity->getModificationTimeStamp());
        $this->concreteEntity->updateTimeStamps();
        $this->assertInstanceOf('\DateTime', $this->concreteEntity->getCreationTimeStamp());
        $this->assertInstanceOf('\DateTime', $this->concreteEntity->getModificationTimeStamp());
    }

    /**
     * Test update method.
     *
     * @return void
     */
    public function testUpdate()
    {
        $updates = array(
            'creator' => 'new_creator',
            'modifier' => 'new_modifier',
        );
        $this->concreteEntity->update($updates);
        $this->assertEquals(
            'new_creator',
            $this->concreteEntity->getCreator()
        );
        $this->assertEquals(
            'new_modifier',
            $this->concreteEntity->getModifier()
        );
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
        $concreteEntityData = array(
            'id' => null,
            'creator' => self::$testCreator,
            'creationTimeStamp' => $timeStampISO,
            'modifier' => self::$testCreator,
            'modificationTimeStamp' => $timeStampISO,
            'name' => null,
        );
        $this->concreteEntity->setCreationTimeStamp($timeStamp);
        $this->assertEquals(json_encode($concreteEntityData), json_encode($this->concreteEntity));
    }

    /**
     * Test that we can control which properties are serialized and specify the order.
     *
     * @return void
     */
    public function testSerializationControl()
    {
        $concreteEntityData = array(
            'id' => null,
            'name' => null,
            'creator' => self::$testCreator,
        );
        $this->concreteEntity->setSerializeProperties(array('id','name','creator'));
        $this->assertEquals(json_encode($concreteEntityData), json_encode($this->concreteEntity));
    }
}

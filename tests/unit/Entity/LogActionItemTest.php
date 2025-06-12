<?php

namespace App\Tests\Entity;

use App\Entity\LogActionItem;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\LogActionItem.
 */
class LogActionItemTest extends TestCase
{
    /**
     * Property to hold an instance of Log Action Item for testing.
     * @var LogActionItem
     */
    protected $logActionItem;

    /**
     * Property to hold an Action Name for testing..
     * @var string
     */
    protected static $testActionName = 'TestAction';

    /**
     * Property to hold a Subject Entity Name for testing.
     * @var string
     */
    protected static $testSubjectEntityName = 'Dataset';

    /**
     * Property to hold a Subject Entity Id for testing.
     * @var integer
     */
    protected static $testSubjectEntityId = '1111';

    /**
     * JSON array property to hold Payload of a log action item for testing.
     * @var integer
     */
    protected static $testPayLoad = array('user' => 'abc@xyz.com','keyName' => 'valueName');

    /**
     * Setup for PHPUnit tests.
     *
     * This instantiates an instance of Entity and sets its properties.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->logActionItem = new LogActionItem(self::$testActionName);
        $this->logActionItem->setSubjectEntityName(self::$testSubjectEntityName);
        $this->logActionItem->setSubjectEntityId(self::$testSubjectEntityId);
        $this->logActionItem->setPayLoad(self::$testPayLoad);
    }

    /**
     * Test the getActionName method.
     *
     * This method should return the Log Action Item that was assigned in setUp.
     *
     * @return void
     */
    public function testGetActionName()
    {
        $this->assertEquals(
            self::$testActionName,
            $this->logActionItem->getActionName()
        );
    }

    /**
     * Test the getSubjectEntityName method.
     *
     * This method should return the Log Action Item that was assigned in setUp.
     *
     * @return void
     */
    public function testGetSubjectEntityName()
    {
        $this->assertEquals(
            self::$testSubjectEntityName,
            $this->logActionItem->getSubjectEntityName()
        );
    }

    /**
     * Test the getSubjectEntityId method.
     *
     * This method should return the Log Action Item that was assigned in setUp.
     *
     * @return void
     */
    public function testGetSubjectEntityId()
    {
        $this->assertEquals(
            self::$testSubjectEntityId,
            $this->logActionItem->getSubjectEntityId()
        );
    }

    /**
     * Test the getPayLoad method.
     *
     * This method should return the Log Action Item that was assigned in setUp.
     *
     * @return void
     */
    public function testGetPayLoad()
    {
        $this->assertEquals(
            self::$testPayLoad,
            $this->logActionItem->getPayLoad()
        );
    }

    /**
     * Test the constructor with all arguments.
     *
     * This method should return the Log Action Item that was assigned in setUp.
     *
     * @return void
     */
    public function testConstructor()
    {
        $testInstance = new LogActionItem(
            self::$testActionName,
            self::$testSubjectEntityName,
            self::$testSubjectEntityId,
            self::$testPayLoad
        );
        $this->assertEquals($this->logActionItem, $testInstance);
    }

  /**
   * Test the constructor in case of a null Subject Entity Id.
   *
   * This method should return the Log Action Item that was assigned in setUp.
   *
   * @return void
   */
    public function testConstructorWithoutSubjectEntityId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Subject Entity Id is required.');
        $this->logActionItem = new LogActionItem(
            self::$testActionName,
            self::$testSubjectEntityName,
            null,
            self::$testPayLoad
        );
    }

    /**
     * Clean up after tests.
     *
     * @return void
     */
    public function tearDow(): void
    {
    }
}

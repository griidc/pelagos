<?php

namespace Pelagos;

/**
 * Unit tests for Pelagos\HTTPStatus.
 *
 * @group Pelagos
 * @group Pelagos\HTTPStatus
 */
class HTTPStatusTest extends \PHPUnit_Framework_TestCase
{
    /** @var int $testCode An HTTP status code to use for testing. **/
    protected static $testCode = 200;

    /** @var string $testMessage A message to use for testing. **/
    protected static $testMessage = 'Success!';

    /**
     * Test that getters return values passed to constructor.
     */
    public function testGetters()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode, self::$testMessage);
        $this->assertEquals(self::$testCode, $status->getCode());
        $this->assertEquals(self::$testMessage, $status->getMessage());
    }

    /**
     * Test that message is null by default if not passed to constructor.
     */
    public function testNullMessageByDefault()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode);
        $this->assertEquals(self::$testCode, $status->getCode());
        $this->assertNull($status->getMessage());
    }

    /**
     * Test that HTTPStatus is JSON serializable and returns expected JSON.
     */
    public function testJsonSerializable()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode, self::$testMessage);
        $this->assertEquals(
            '{"code":' . self::$testCode . ',"message":"' . self::$testMessage . '"}',
            json_encode($status)
        );
    }

    /**
     * Test that asJSON() returns expected JSON.
     */
    public function testAsJSON()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode, self::$testMessage);
        $this->assertEquals(
            '{"code":' . self::$testCode . ',"message":"' . self::$testMessage . '"}',
            $status->asJSON()
        );
    }
}

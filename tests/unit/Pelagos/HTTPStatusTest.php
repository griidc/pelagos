<?php

namespace Pelagos;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Pelagos\HTTPStatus.
 *
 * @group Pelagos
 * @group Pelagos\HTTPStatus
 */
class HTTPStatusTest extends TestCase
{
    /**
     * An HTTP status code to use for testing.
     *
     * @var int $testCode
     */
    protected static $testCode = 200;

    /**
     * A message to use for testing.
     *
     * @var string $testMessage
     */
    protected static $testMessage = 'Success!';

    /**
     * A data package to use for testing.
     *
     * @var array $testData
     */
    protected static $testData = array(
        'foo' => 1,
        'bar' => 2,
        'baz' => 3,
    );

    /**
     * Test that getters return values passed to constructor.
     *
     * @return void
     */
    public function testGetters()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode, self::$testMessage, self::$testData);
        $this->assertEquals(self::$testCode, $status->getCode());
        $this->assertEquals(self::$testMessage, $status->getMessage());
        $this->assertEquals(self::$testData, $status->getData());
    }

    /**
     * Test that message is null by default if not passed to constructor.
     *
     * @return void
     */
    public function testNullMessageByDefault()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode);
        $this->assertEquals(self::$testCode, $status->getCode());
        $this->assertNull($status->getMessage());
    }

    /**
     * Test that data is null by default if not passed to constructor.
     *
     * @return void
     */
    public function testNullDataByDefault()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode);
        $this->assertNull($status->getData());
    }

    /**
     * Test that HTTPStatus is JSON serializable and returns expected JSON.
     *
     * @return void
     */
    public function testJsonSerializable()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode, self::$testMessage, self::$testData);
        $this->assertEquals(
            $this->makeHTTPStatusJSON(self::$testCode, self::$testMessage, self::$testData),
            json_encode($status)
        );
    }

    /**
     * Test that HTTPStatus is JSON serializable and returns expected JSON when data is not set.
     *
     * @return void
     */
    public function testJsonSerializableNoData()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode, self::$testMessage);
        $this->assertEquals(
            $this->makeHTTPStatusJSON(self::$testCode, self::$testMessage),
            json_encode($status)
        );
    }

    /**
     * Test that asJSON() returns expected JSON.
     *
     * @return void
     */
    public function testAsJSON()
    {
        $status = new \Pelagos\HTTPStatus(self::$testCode, self::$testMessage);
        $this->assertEquals(
            $this->makeHTTPStatusJSON(self::$testCode, self::$testMessage),
            $status->asJSON()
        );
    }

    /**
     * Utility method to build a JSON string equivalent to a JSON serialized HTTPStatus.
     *
     * @param integer $code    The HTTP status code.
     * @param string  $message The HTTP status message.
     * @param mixed   $data    The data package.
     *
     * @return string A JSON string containing $code, $message, and $data (if set).
     */
    protected function makeHTTPStatusJSON($code, $message = null, $data = null)
    {
        $serialized = array(
            'code' => $code,
            'message' => $message,
        );
        if (isset($data)) {
            $serialized['data'] = $data;
        }
        return json_encode($serialized);
    }
}

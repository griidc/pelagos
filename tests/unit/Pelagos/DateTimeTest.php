<?php

namespace Pelagos;

use Symfony\Component\Validator\Validation;
use \Pelagos\DateTime as DateTime;
use \Pelagos\Date as Date;

/**
 * Class DateTimeTest.
 *
 * A unit test for the DateTime class.
 */
class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    private $referenceDateTime = null;
    private $laterDateTime = null;
    private $currentDateTime = null;

    private static $year = 1996;
    private static $month = 10;
    private static $day = 7;

    private static $hour = 13;
    private static $minute = 33;
    private static $second = 55;

    private static $referenceFormat = "Y-m-d:H-i-s";
    private static $referenceFormatResult = "1996-10-07:13-33-55";

    /**
     * Setup for PHPUnit tests
     *
     * @return void
     */
    protected function setUp()
    {
        $this->referenceDateTime = new DateTime();
        $this->referenceDateTime->setDate(self::$year, self::$month, self::$day);
        $this->referenceDateTime->setTime(self::$hour, self::$minute, self::$second);
        $this->referenceDateTime->setFormat(self::$referenceFormat);

    }


    /**
     * Test the ability to set the format.
     * Set the referenceDateTime to the reference format
     * and compare the output to expected results.
     *
     * @return void
     */
    public function testSetFormat()
    {
        $this->referenceDateTime->setFormat(self::$referenceFormat);
        $this->expectOutputString(self::$referenceFormatResult);
        print $this->referenceDateTime;
    }

    /**
     * Test the comparison of two dates two seconds apart
     */
    public function testComparison()
    {
        $this->currentDateTime = new DateTime();
        $t = $this->currentDateTime->getTimestamp();
        $this->laterDateTime = new DateTime();
        $this->laterDateTime->setTimestamp($t+2000);
        $this->assertTrue($this->laterDateTime > $this->currentDateTime);
    }

    /**
     * Test the setting of one date to the value of another
     * Compare for equality
     */
    public function testSet()
    {
        $oneDateTime = new DateTime();
        $twoDateTime = new DateTime();
        $twoDateTime->set($oneDateTime);

        $this->assertEquals($oneDateTime, $twoDateTime);
    }
}

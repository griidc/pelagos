<?php

namespace Pelagos;

/**
 * Class DateTimeTest.
 *
 * A unit test for the DateTime class.
 */
class DateTimeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the ability to set the format.
     *
     * Set the referenceDateTime to the reference format
     * and compare the output to expected results.
     *
     * @return void
     */
    public function testSetFormat()
    {

        $referenceFormat = 'Y-m-d:H-i-s';
        $referenceFormatResult = '1996-10-07:13-33-55';

        $referenceDateTime = new Date();
        $referenceDateTime->setDate(1996, 10, 7);
        $referenceDateTime->setTime(13, 33, 55);

        $referenceDateTime->setFormat($referenceFormat);

        $this->assertEquals($referenceFormatResult, (string) $referenceDateTime);
    }

    /**
     * Test the comparison of two dates two seconds apart.
     *
     * @return void
     */
    public function testComparison()
    {
        $currentDateTime = new DateTime();
        $t = $currentDateTime->getTimestamp();
        $laterDateTime = new DateTime();
        $laterDateTime->setTimestamp($t + 2000);
        $this->assertTrue($laterDateTime > $currentDateTime);
    }

    /**
     * Test the setting of one date to the value of another.
     *
     * Compare for equality
     *
     * @return void
     */
    public function testSet()
    {
        $oneDateTime = new DateTime();
        $twoDateTime = new DateTime();
        $twoDateTime->set($oneDateTime);

        $this->assertEquals($oneDateTime, $twoDateTime);
    }
}

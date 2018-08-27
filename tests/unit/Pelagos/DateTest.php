<?php
namespace Pelagos;

use PHPUnit\Framework\TestCase;

/**
 * DateTest.php A unit test for the Pelagos Date class.
 *
 * @package Pelagos
 */
class DateTest extends TestCase
{
    /**
     * Test the ability to set the format.
     *
     * Set the referenceDate to the reference format
     * and compare the output to expected results.
     *
     * The output should not produce hours, minutes and seconds,
     * only the year, month and day
     *
     * @return void
     */
    public function testDefaultFormat()
    {
        $referenceDate = new Date();
        $referenceDate->setDate(1996, 10, 7);
        $referenceDate->setTime(13, 33, 55);

        $expectedResult = '1996-10-07';

        $this->assertEquals($expectedResult, (string) $referenceDate);
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
        $oneDate = new Date();
        $twoDate = new Date();
        $twoDate->set($oneDate);

        $this->assertEquals($oneDate, $twoDate);
    }
}

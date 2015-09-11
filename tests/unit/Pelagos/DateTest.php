<?php
/**
 * DateTest.php
 *
 * @package Pelagos
 * Texas A&M Corpus Christi
 * Harte Research Institute
 * Gulf (of Mexico) Research Initiative Information Data Cooperative ( GRIIDC )
 */

namespace Pelagos;


use Symfony\Component\Validator\Validation;
use \Pelagos\DateTime as DateTime;
use \Pelagos\Date as Date;

/**
 * DateTest.php Short description goes here. (Includes - GRIIDC PHP CLASS FILE DOC).
 *
 * @package Pelagos
 * Texas A&M Corpus Christi
 * Harte Research Institute
 * Gulf (of Mexico) Research Initiative Information Data Cooperative ( GRIIDC )
 */

class DateTest extends \PHPUnit_Framework_TestCase
{
    private $referenceDate = null;
    private $laterDate = null;
    private $currentDate = null;

    private static $year = 1996;
    private static $month = 10;
    private static $day = 7;

    private static $hour = 13;
    private static $minute = 33;
    private static $second = 55;

    private static $expectedResult = "1996-10-07";

    /**
     * Setup for PHPUnit tests
     *
     * @return void
     */
    protected function setUp()
    {
        $this->referenceDate = new Date();
        $this->referenceDate->setDate(self::$year,self::$month,self::$day);
        $this->referenceDate->setTime(self::$hour,self::$minute,self::$second);
    }


    /**
     * Test the ability to set the format.
     * Set the referenceDate to the reference format
     * and compare the output to expected results.
     *
     * @return void
     */
    public function testDefaultFormat()
    {
        $this->referenceDate->setDate(self::$year,self::$month,self::$day);
        $this->referenceDate->setTime(self::$hour,self::$minute,self::$second);
        $this->expectOutputString(self::$expectedResult);
        print $this->referenceDate;
    }



    /**
     * Test the setting of one date to the value of another
     * Compare for equality
     */
    public function testSet()
    {
        $oneDate = new Date();
        $twoDate = new Date();
        $twoDate->set($oneDate);

        $this->assertEquals($oneDate, $twoDate);
    }
}
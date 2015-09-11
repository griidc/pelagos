<?php
/**
 * Date a simple date class that prints date only, not time.
 *
 * @package Pelagos
 */

namespace Pelagos;

/**
 * Class Date
 *
 * This class extends Pelagos\DateTime but limits its
 * to string output to date only year, month day , no time represented.
 * The _toString functionality is provided by the base class
 * \Pelagos\DateTime
 * @package Pelagos
 * @see \Pelagos\DateTime
 */
class Date extends DateTime  // this is \Pelagos\DateTime
{
    public static $DefaultFormat = 'Y-m-d';

    /**
     * Constructor implemented so that the format can be set.
     * Attribute format is in the base class DateTime. Set it to
     * the default of this class Date.
     * @param null $time
     * @param DateTimeZone $timezone
     */
    public function __construct( $time = NULL, DateTimeZone $timezone = NULL)
    {
       parent::__construct($time, $timezone);
        $this->setFormat(Date::$DefaultFormat);
    }
}
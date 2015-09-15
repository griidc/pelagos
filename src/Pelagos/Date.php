<?php

namespace Pelagos;

use \DateTimeZone as DateTimeZone;

/**
 * Class Date extending DateTime mostly for __toString.
 *
 * This class extends Pelagos\DateTime but limits its
 * to string output to date only year, month day , no time represented.
 * The _toString functionality is provided by the base class
 * \Pelagos\DateTime
 *
 * @package Pelagos
 * @see \Pelagos\DateTime
 */
class Date extends DateTime
{
    /**
     * The specification of the formatting string.
     *
     * @var string
     */
    public static $DefaultFormat = 'Y-m-d';

    /**
     * Constructor implemented so that the format can be set.
     *
     * Attribute format is in the base class DateTime. Set it to
     * the default of this class Date.
     *
     * @param null         $time     A value of the time for the new Object.
     * @param DateTimeZone $timezone The time zone to use in the object.
     */
    public function __construct($time = null, DateTimeZone $timezone = null)
    {
         parent::__construct($time, $timezone);
         $this->setFormat(Date::$DefaultFormat);
    }
}

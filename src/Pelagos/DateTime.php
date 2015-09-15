<?php

namespace Pelagos;

use \DateTime as DateTime;

/**
 * DateTime an extension of \DateTime that prints via toString.
 *
 * The primary function of this class is that
 * it changes the behaviour of function _toString
 * to that of returning a string formatted to the ISO standard
 * format the GRIIDC prefers.
 */
class DateTime extends \DateTime
{
    /**
     * The format to use by default for formatting this value as a string.
     *
     * @var string
     */
    public static $DefaultFormat = \DateTime::ISO8601;
    /**
     * The format to use for formatting this value as a string.
     *
     * This value can be changed via setFormat().
     *
     * @var string
     */
    private $formatString =  \DateTime::ISO8601;

    /**
     * Return the format used  by default for formatting as a string.
     *
     * @return string Return description goes here.
     * Throws description goes here.
     */
    public static function getDefaultFormat()
    {
        return self::$DefaultFormat;
    }
    /**
     * Change the value in the format attribute.
     *
     * This is used by toString in pretty printing.
     *
     * @param string $formatString The formatting string to be used by this object.
     *
     * @return void
     */
    public function setFormat($formatString)
    {
        $this->formatString = $formatString;
    }

    /**
     * Set this DateTime with the value of another like object.
     *
     * @param DateTime $dt Another DateTime object that you wish to copy.
     *
     * @return void
     */
    public function set(DateTime $dt)
    {
        $this->setTimestamp($dt->getTimestamp());
    }

    /**
     * A Pretty Print sort of formatting in the string context.
     *
     * @return string The formatted string.
     */
    public function __toString()
    {
        return $this->format($this->formatString);
    }
}

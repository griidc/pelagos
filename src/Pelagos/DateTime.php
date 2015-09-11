<?php
/**
 * DateTime an extension of \DateTime that prints via toString
 * with a prescribed format.
 */

namespace Pelagos;

/**
 * Class An extension of \DateTime.
 * The primary function of this class is that
 * it changes the behaviour of function _toString
 * to that of returning a string formatted to the ISO standard
 * format the GRIIDC prefers.
 */
class DateTime  extends \DateTime {

    public static $DefaultFormat = \DateTime::ISO8601;

    private $format =  \DateTime::ISO8601;

    public static function getDefaultFormat() {
      return self::$DefaultFormat;
    }
    /**
     * Change the value in the format attribute.
     * This is used by toString in pretty printing.
     * @param string $formatString
     *
     */
    public function setFormat($formatString)
    {
        $this->format = $formatString;
    }

    /**
     * Set this DateTime with the value of another like object
     * @param \DateTime $dt - Another DateTime object that you wish to copy
     *
     */
    public function set(DateTime $dt) {
        $this->setTimestamp($dt->getTimestamp());
    }

    /**
     *A Pretty Print sort of formatting in the string context
     *
     * @return string the formatted string
     */
    public function __toString()
    {
        return $this->format($this->format);
    }
}
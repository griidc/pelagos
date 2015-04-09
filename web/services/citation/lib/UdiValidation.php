<?php

namespace Citation;

/**
 * A class to validate a UDI represented as a string
 */
class UdiValidation
{


    const REGXRYT = "[R|Y|T]{1}";
    const REGXDOT = "\.";
    const REGXCOLON = "\:";
    const REGXX = "x{1}";
    const REGX09_1 = "[0-9|S]{1}";
    const REGX09_3 = "[0-9]{3}";
    const REGX09_4 = "[0-9]{4}";
    const UDI_MIN_LENGTH = 16;
    const UDI_MAX_LENGTH = 20;

    public function __construct()
    {

    }

    /**
     * @return string that is a pattern that matches a conforming udi such as
     * R4.x260.204:0001
     */
    private function getShortUdiPattern()
    {
        $regX = "/" .
            self::REGXRYT .  // R
            self::REGX09_1 . // 4
            self::REGXDOT .  // .
            self::REGXX .    //  x
            self::REGX09_3 . // 260
            self::REGXDOT .  // .
            self::REGX09_3 .  // 204
            self::REGXCOLON .  // .
            self::REGX09_4 . //0001
            "/";
        return $regX;
    }

    /**
     * @return string that is a pattern that matches a conforming udi such as
     * R4.x260.204:0001
     */
    private function getLongUdiPattern()
    {
        $regX = "/" .
            self::REGXRYT .  // R
            self::REGX09_1 . // 4
            self::REGXDOT .  // .
            self::REGXX .    //  x
            self::REGX09_3 . // 260
            self::REGXDOT .  // .
            self::REGX09_3 .  // 204
            self::REGXCOLON .  // .
            self::REGX09_4 . //0001
            self::REGXDOT .  // .
            self::REGX09_3 . // 260
            "/";
        return $regX;
    }

    /**
     * Test the udi for integrity.
     * If any test fails throw an exception.
     * If all tests succeed return the valid udi string.
     *
     * @param $udi
     * @return boolean
     * @throws InvalidUdiException
     */
    public function validate($udi) // throws InvalidUdiException
    {
        $this->isValidString($udi);
        $this->isCorrectLength($udi);
        $udi = $this->isValidPattern($udi);
        return $udi;
    }

    /**
     * If the $string does not contain a substring in the form
     * of a udi, throw InvalidUdiException.
     * If a valid udi is a substring of the $targetString,
     * return the udi string found;
     * @param $udi - a string to identify the dataset
     * @return true if the udi contains the expected pattern
     * otherwise throw InvalidUdiException
     * @see getShortUdiPattern()
     * @see getLongUdiPattern
     */
    private function isValidPattern($udi) // throws InvalidUdiException
    {
        require_once './lib/InvalidUdiException.php';
        $out = "";  // the output   buffer for pgreg_match

        preg_match($this->getLongUdiPattern(), $udi, $out);
        //  even if it matches the long pattern return the short version
        if (count($out) > 0) {
            preg_match($this->getShortUdiPattern(), $udi, $out);
            return $out[0];
        }

        preg_match($this->getShortUdiPattern(), $udi, $out);
        if (count($out) > 0) {
            return $out[0];
        }

        throw new \Citation\InvalidUdiException("ERROR: UDI " . $udi . " does not conform to expected pattern");
    }

    /**
     * @param $udi
     * @return true if the udi is a non null string
     * otherwise throw InvalidUdiException
     */
    private function isValidString($udi) // throws InvalidUdiException
    {
        require_once './lib/InvalidUdiException.php';
        if (!empty($udi)) {
            return true;
        }
        throw new \Citation\InvalidUdiException("ERROR: The UDI parameter provided is empty");
    }


    /**
     * A UDI should be sixteen characters if in proper form.
     * Return true if the length is correct, throw exception otherwise.
     * @param $udi
     * @return boolean
     * @throws InvalidUdiException
     */
    private function isCorrectLength($udi)
    {
        $len = strlen($udi);

        if ($len == self::UDI_MIN_LENGTH ||
            $len == self::UDI_MAX_LENGTH) {
            return true;
        }
        $msg = "must be " . self::UDI_MIN_LENGTH . " or " . self::UDI_MAX_LENGTH . " characters in length.";
        require_once './lib/InvalidUdiException.php';
        throw new \Citation\InvalidUdiException("ERROR: UDI provided (" . $udi . ") " . $msg);

        return true;
    }
}

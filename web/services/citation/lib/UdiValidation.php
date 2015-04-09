<?php


/**
 * A class to validate a UDI represented as a string
 */


class UdiValidation {


    const RegXryt = "[R|Y|T]{1}";
    const RegXdot = "\.";
    const RegXcolon = "\:";
    const RegXx = "x{1}";
    const RegX09_1 = "[0-9|S]{1}";
    const RegX09_3 = "[0-9]{3}";
    const RegX09_4 = "[0-9]{4}";
    const UdiExpectedLength = 16;

    public function __construct() {

    }

    /**
     * @return string that is a pattern that matches a conforming udi such as
     * R4.x260.204:0001
     */
    private function getUdiPattern()
    {
        $regX = "/" .
            self::RegXryt .  // R
            self::RegX09_1 . // 4
            self::RegXdot .  // .
            self::RegXx .    //  x
            self::RegX09_3 . // 260
            self::RegXdot .  // .
            self::RegX09_3 .  // 204
            self::RegXcolon .  // .
            self::RegX09_4 . //0001
            "/";
        return $regX;
    }

    /**
     * @param $s
     * @return string
     */
    private function extractUdiFromString($s) {
        $out = "";
        preg_match($this->getUdiPattern(),$s,$out);
        return $out;
    }

    /**
     * Test the udi for integrity.
     * If any test fails throw an exception.
     * If all tests succeed return true.
     *
     * @param $udi
     * @return boolean
     * @throws InvalidUdiException
     */
    public function validate($udi) {  // throws InvalidUdiException
        $this->isValidString($udi);
        $this->isCorrectLength($udi);
        $this->isValidPattern($udi);
        return true;
    }
    /**
     * If the $string does not contain a substring in the form
     * of a udi, throw InvalidUdiException.
     * If a valid udi is a substring of the $targetString,
     * return the udi string found;
     * @param $udi - a string to identify the dataset
     * @return true if the udi contains the expected pattern
     * otherwise throw InvalidUdiException
     * @see getUdiPattern()
     */
    private function isValidPattern($udi) {  // throws InvalidUdiException
        $out = "";
        $b = preg_match($this->getUdiPattern(),$udi,$out);
        if($b) return $out[0];
        require_once './lib/InvalidUdiException.php';
        throw new InvalidUdiException("ERROR: UDI ".$udi." does not conform to expected pattern");
    }
    /**
     * @param $udi
     * @return true if the udi is a non null string
     * otherwise throw InvalidUdiException
     */
    private function isValidString($udi) {  // throws InvalidUdiException
        require_once './lib/InvalidUdiException.php';
        if(!empty($udi)) {
            return true;
        }
        throw new InvalidUdiException("ERROR: The UDI parameter provided is empty");
    }


    /**
     * A UDI should be sixteen characters if in proper form.
     * Return true if the length is correct, throw exception otherwise.
     * @param $udi
     * @return boolean
     * @throws InvalidUdiException
     */
    private function isCorrectLength($udi) {
        $len = strlen($udi);

        if ($len != self::UdiExpectedLength) {
            $msg = "must be " . self::UdiExpectedLength . " characters in length.";
            require_once './lib/InvalidUdiException.php';
            throw new InvalidUdiException("ERROR: UDI provided (" . $udi . ") " . $msg);
        }

        return true;
    }
}
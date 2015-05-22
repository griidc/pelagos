<?php
/**
 * UnsupportedClassException
 * Texas A&M Corpus Christi
 * Harte Research Institute
 * Gulf (of Mexico) Research Initiative Information Data Cooperative
 * GRIIDC
 */

namespace Exception;

/**
 * Class UnsupportedClassException
 * @package Exception
 * An exception to throw when the throwing code
 * does not know how to deal the class in question.
 */

class UnsupportedClassException   extends \Exception {

    public function __construct($message) {
        parent::__construct($message);
    }
}
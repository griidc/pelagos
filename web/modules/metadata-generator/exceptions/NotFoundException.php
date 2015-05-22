<?php
/**
 * NotFoundException
 * Texas A&M Corpus Christi
 * Harte Research Institute
 * Gulf (of Mexico) Research Initiative Information Data Cooperative
 * GRIIDC
 */

namespace Exception;

/**
 * Class NotFoundException
 * @package Exception
 * An exception to be thrown in a context
 * in which an object or some data is
 * to be retrieved but can not be found.
 * This can be thrown and caught instead of
 * returning null.
 */

class NotFoundException extends \Exception {

    public function __construct($message) {
        parent::__construct($message);
    }
}
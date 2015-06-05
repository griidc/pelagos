<?php
/**
 * DuplicateException
 * Texas A&M Corpus Christi
 * Harte Research Institute
 * Gulf (of Mexico) Research Initiative Information Data Cooperative
 * GRIIDC
 */

namespace Exception;

/**
 * Class InvalidXmlException
 * @package Exception
 * An exception to be thrown when text
 * expected to be XML does not validate
 */
class InvalidXmlException extends \Exception
{

    public function __construct($message)
    {
        parent::__construct($message);
    }
}

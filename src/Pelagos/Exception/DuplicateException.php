<?php
/**
 * DuplicateException
 * Texas A&M Corpus Christi
 * Harte Research Institute
 * Gulf (of Mexico) Research Initiative Information Data Cooperative
 * GRIIDC
 */

namespace Pelagos\Exception;

/**
 * Class DuplicateException
 * @package Exception
 * An exception to be thrown when an application
 * or service tries to store or otherwise make a
 * duplicate in a context in which duplicates are
 * not allowed.
 */
class DuplicateException extends \Exception
{
}

<?php

namespace Pelagos\Exception;

/**
 * Class DuplicateException.
 *
 * An exception to be thrown when an application
 * or service tries to store or otherwise make a
 * duplicate in a context in which duplicates are
 * not allowed.
 *
 * @package Exception
 */
class DuplicateException extends \Exception
{
}

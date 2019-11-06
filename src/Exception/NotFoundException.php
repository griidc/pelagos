<?php

namespace Pelagos\Exception;

/**
 * Class NotFoundException.
 *
 * An exception to be thrown in a context
 * in which an object or some data is
 * to be retrieved but can not be found.
 * This can be thrown and caught instead of
 * returning null.
 */
class NotFoundException extends \Exception
{
}

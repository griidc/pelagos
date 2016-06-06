<?php

namespace Pelagos\Exception\POSIXify;

/**
 * Class AccountAlreadyPOSIXEnabledException.
 *
 * An exception to be thrown when an someone requests
 * an account be converted to a POSIX account but the
 * account already is a POSIX account.
 *
 * @package Exception
 */
class AccountAlreadyPOSIXEnabledException extends \Exception
{
}

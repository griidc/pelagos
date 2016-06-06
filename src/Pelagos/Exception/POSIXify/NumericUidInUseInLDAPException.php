<?php

namespace Pelagos\Exception\POSIXify;

/**
 * Class NumericUidInUseInLDAPException.
 *
 * An exception to be thrown when an someone requests POSIX
 * conversion of their account and the numeric UID is already
 * in use in the LDAP.
 *
 * @package Exception
 */
class NumericUidInUseInLDAPException extends \Exception
{
}

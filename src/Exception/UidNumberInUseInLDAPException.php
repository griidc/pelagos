<?php

namespace Pelagos\Exception;

/**
 * Class UidNumberInUseInLDAPException.
 *
 * An exception to be thrown when creating or updating
 * an LDAP account an the UID Number being set is
 * already in use on LDAP.
 *
 * @package Exception
 */
class UidNumberInUseInLDAPException extends \Exception
{
}

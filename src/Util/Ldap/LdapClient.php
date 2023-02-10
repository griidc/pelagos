<?php

namespace App\Util\Ldap;

use Symfony\Component\Ldap\Adapter\AdapterInterface;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\Exception\LdapException;
use Psr\Log\LoggerInterface;

/**
 * An LDAP client class.
 */
class LdapClient
{
    /**
     * A Monolog Logger.
     */
    protected LoggerInterface $logger;

    /**
     * The hostname to connect to.
     *
     * @var string
     */
    protected $host;

    /**
     * The LDAP port to connect to.
     *
     * @var integer
     */
    protected $port;

    /**
     * The version of LDAP to use.
     *
     * @var integer
     */
    protected $version;

    /**
     * Whether or not to use SSL when connecting.
     *
     * @var boolean
     */
    protected $useSsl;

    /**
     * Whether or not to use StartTls.
     *
     * @var boolean
     */
    protected $useStartTls;

    /**
     * Whether or not to use referrals.
     *
     * @var mixed
     */
    protected $optReferrals;

    /**
     * An LDAP link identifier.
     *
     * @var resource
     */
    protected $connection;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger     A PSR Logger.
     * @param string  $host         The hostname to connect to.
     * @param integer $port         The LDAP port to connect to.
     * @param integer $version      The version of LDAP to use.
     * @param boolean $useSsl       Whether or not to use SSL when connecting.
     * @param boolean $useStartTls  Whether or not to use StartTls.
     * @param boolean $optReferrals Whether or not to use
     *
     * @throws LdapException When the ldap module is not loaded.
     */
    public function __construct(
        LoggerInterface $logger,
        string $host = null,
        int $port = 389,
        int $version = 3,
        bool $useSsl = false,
        bool $useStartTls = false,
        bool $optReferrals = false,
    ) {
        if (!extension_loaded('ldap')) {
            throw new LdapException('The ldap module is needed.');
        }

        $this->logger = $logger;
        $this->host = $host;
        $this->port = $port;
        $this->version = $version;
        $this->useSsl = (bool) $useSsl;
        $this->useStartTls = (bool) $useStartTls;
        $this->optReferrals = (bool) $optReferrals;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Return a connection bound to the ldap.
     *
     * @param string $dn       An LDAP dn.
     * @param string $password A password.
     *
     * @throws ConnectionException If dn / password could not be bound.
     *
     * @return void
     */
    public function bind(string $dn = null, string $password = null)
    {
        if (!$this->connection) {
            $this->connect();
        }

        if (false === @ldap_bind($this->connection, $dn, $password)) {
            throw new ConnectionException(ldap_error($this->connection));
        }
    }

    /**
     * Find a username into ldap connection.
     *
     * @param string $dn     An LDAP dn.
     * @param string $query  The search filter.
     * @param mixed  $filter The attributes to return.
     *
     * @return array|null
     */
    public function find(string $dn, string $query, $filter = '*')
    {
        if (!is_array($filter)) {
            $filter = array($filter);
        }

        $search = ldap_search($this->connection, $dn, $query, $filter);
        $infos = ldap_get_entries($this->connection, $search);

        if (0 === $infos['count']) {
            return null;
        }

        return $infos;
    }

    /**
     * Escape a string for use in an LDAP filter or DN.
     *
     * @param string  $subject The value to escape.
     * @param string  $ignore  Characters to ignore when escaping.
     * @param integer $flags   Flags to set while escaping.
     *
     * @see ldap_escape
     *
     * @return string
     */
    public function escape(string $subject, string $ignore = '', int $flags = 0)
    {
        $value = ldap_escape($subject, $ignore, $flags);

        // Per RFC 4514, leading/trailing spaces should be encoded in DNs, as well as carriage returns.
        if (((int) $flags & LDAP_ESCAPE_DN)) {
            if (!empty($value) && $value[0] === ' ') {
                $value = '\\20' . substr($value, 1);
            }
            if (!empty($value) && $value[(strlen($value) - 1)] === ' ') {
                $value = substr($value, 0, -1) . '\\20';
            }
            $value = str_replace("\r", '\0d', $value);
        }

        return $value;
    }

    /**
     * Add an entry to the LDAP directory.
     *
     * @param string $dn    Distinguished name of an LDAP entity to add.
     * @param array  $entry An array that specifies the information about the entry.
     *                      The values in the entries are indexed by individual attributes.
     *                      In case of multiple values for an attribute, they are indexed
     *                      using integers starting with 0.
     *
     * @throws LdapException When the add fails.
     *
     * @return void
     */
    public function add(string $dn, array $entry)
    {
        if (false === @ldap_add($this->connection, $dn, $entry)) {
            throw new LdapException(ldap_error($this->connection));
        }
    }

    /**
     * Modify an entry in the LDAP directory.
     *
     * @param string $dn    Distinguished name of an LDAP entity to modify.
     * @param array  $entry An array that specifies the information about the entry.
     *                      The values in the entries are indexed by individual attributes.
     *                      In case of multiple values for an attribute, they are indexed
     *                      using integers starting with 0.
     *
     * @throws LdapException When the modify fails.
     *
     * @return void
     */
    public function modify(string $dn, array $entry)
    {
        if (false === @ldap_modify($this->connection, $dn, $entry)) {
            throw new LdapException(ldap_error($this->connection));
        }
    }

    /**
     * Rename (move) an entry in the LDAP directory.
     *
     * This method currently only works with LDAPv3.
     *
     * @param string      $dn           Distinguished name of an LDAP entity to rename.
     * @param string      $newRdn       New relative distinguished name of the LDAP entity.
     * @param string|null $newParent    Distinguished name of the new parent LDAP entity.
     *                                  Null keeps the same parent.
     * @param boolean     $deleteOldRdn Whether or not to delete the old RDN value(s).
     *
     * @throws LdapException When the rename fails.
     *
     * @return void
     */
    public function rename(string $dn, string $newRdn, $newParent = null, bool $deleteOldRdn = true)
    {
        if (false === @ldap_rename($this->connection, $dn, $newRdn, $newParent, $deleteOldRdn)) {
            throw new LdapException(ldap_error($this->connection));
        }
    }

    /**
     * Delete an entry from the LDAP directory.
     *
     * @param string $dn Distinguished name of an LDAP entity to delete.
     *
     * @throws LdapException When the delete fails.
     *
     * @return void
     */
    public function delete(string $dn)
    {
        if (false === @ldap_delete($this->connection, $dn)) {
            throw new LdapException(ldap_error($this->connection));
        }
    }

    /**
     * Add attribute values to attributes of an entry in the LDAP directory.
     *
     * @param string $dn    Distinguished name of an LDAP entity to modify.
     * @param array  $entry An array that specifies the information about the entry.
     *                      The values in the entries are indexed by individual attributes.
     *                      In case of multiple values for an attribute, they are indexed
     *                      using integers starting with 0.
     *
     * @throws LdapException When the add fails.
     *
     * @return void
     */
    public function addValues(string $dn, array $entry)
    {
        if (false === @ldap_mod_add($this->connection, $dn, $entry)) {
            throw new LdapException(ldap_error($this->connection));
        }
    }

    /**
     * Replace the value of an attribute of an entry in the LDAP directory.
     *
     * This performs the same function as modify, but at the attribute level.
     *
     * @param string $dn    Distinguished name of an LDAP entity to modify.
     * @param array  $entry An array that specifies the information about the entry.
     *                      The values in the entries are indexed by individual attributes.
     *                      In case of multiple values for an attribute, they are indexed
     *                      using integers starting with 0.
     *
     * @throws LdapException When the replace fails.
     *
     * @return void
     */
    public function replaceValues(string $dn, array $entry)
    {
        if (false === @ldap_mod_replace($this->connection, $dn, $entry)) {
            throw new LdapException(ldap_error($this->connection));
        }
    }

    /**
     * Remove attribute values from attributes of an entry in the LDAP directory.
     *
     * @param string $dn    Distinguished name of an LDAP entity to modify.
     * @param array  $entry An array that specifies the information about the entry.
     *                      The values in the entries are indexed by individual attributes.
     *                      In case of multiple values for an attribute, they are indexed
     *                      using integers starting with 0.
     *
     * @throws LdapException When the delete fails.
     *
     * @return void
     */
    public function deleteValues(string $dn, array $entry)
    {
        if (false === @ldap_mod_del($this->connection, $dn, $entry)) {
            throw new LdapException(ldap_error($this->connection));
        }
    }

    /**
     * Connect if not already connected.
     *
     * @return void
     */
    protected function connect()
    {
        if (!$this->connection) {
            $host = $this->host;

            if ($this->useSsl) {
                $host = 'ldaps://' . $host;
            }

            $this->connection = ldap_connect($host, $this->port);

            ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, $this->version);
            ldap_set_option($this->connection, LDAP_OPT_REFERRALS, $this->optReferrals);

            if ($this->useStartTls) {
                ldap_start_tls($this->connection);
            }
        }
    }

    /**
     * Disconnect if connected.
     *
     * @return void
     */
    protected function disconnect()
    {
        if ($this->connection && is_resource($this->connection)) {
            ldap_unbind($this->connection);
        }

        $this->connection = null;
    }
}

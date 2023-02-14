<?php

namespace App\Util\Ldap;

use Symfony\Component\PropertyAccess\PropertyAccess;
use App\Entity\Person;
use App\Exception\UidNumberInUseInLDAPException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Ldap\Exception\LdapException;

/**
 * An LDAP abstraction library.
 */
class Ldap
{
    /**
     * An instance of the LDAP client.
     *
     * @var LdapClient
     */
    protected $ldapClient;

    /**
     * The organizational unit that contains people.
     *
     * @var string
     */
    protected $peopleOu;

    /**
     * A Monolog Logger.
     */
    protected LoggerInterface $logger;

    /**
     * Constructor.
     *
     * @param LdapClient      $ldapClient An instance of the LDAP client.
     * @param string          $ldapBindDn The distinguished name to bind with.
     * @param string          $ldapBindPw The password to bind with.
     * @param string          $peopleOu   The OU which contains people.
     * @param LoggerInterface $logger     A PSR Logger.
     */
    public function __construct(LdapClient $ldapClient, string $ldapBindDn, string $ldapBindPw, string $peopleOu, LoggerInterface $logger)
    {
        $this->ldapClient = $ldapClient;
        $this->peopleOu = $peopleOu;
        $this->logger = $logger;
        try {
            $this->ldapClient->bind($ldapBindDn, $ldapBindPw);
        } catch (\Exception $exception) {
            $this->logger->error('LDAP Error: ' . $exception->getMessage());
        }
    }

    /**
     * Add a Person to LDAP.
     *
     * @param Person $person The Person to add to LDAP.
     *
     * @throws UidNumberInUseInLDAPException In event UID is already found in the LDAP.
     *
     * @return void
     */
    public function addPerson(Person $person)
    {
        $uidNumber = $person->getAccount()->getUidNumber();
        if ($this->checkIfUidNumberAvailable($uidNumber)) {
            $ldapPerson = $this->buildLdapPerson($person);
            try {
                $this->ldapClient->add($ldapPerson['dn'], $ldapPerson['entry']);
            } catch (LdapException $e) {
                $this->logger->error("LDAP Error: " . $e->getMessage());
            }
        } else {
            throw new UidNumberInUseInLDAPException("This UID number ($uidNumber) is already in use in LDAP .");
        }
    }

    /**
     * Update a Person in LDAP.
     *
     * @param Person $person The Person to update in LDAP.
     *
     * @throws UidNumberInUseInLDAPException In event UID is already found in the LDAP.
     *
     * @return void
     */
    public function updatePerson(Person $person)
    {
        $uidNumber = $person->getAccount()->getUidNumber();
        if ($this->checkIfUidNumberAvailable($uidNumber, $person->getAccount()->getUserName())) {
            $ldapPerson = $this->buildLdapPerson($person);
            $this->ldapClient->modify($ldapPerson['dn'], $ldapPerson['entry']);
        } else {
            throw new UidNumberInUseInLDAPException("This UID number ($uidNumber) is already in use in LDAP .");
        }
    }

    /**
     * Delete a Person from LDAP.
     *
     * @param Person $person The Person to delete from LDAP.
     *
     * @return void
     */
    public function deletePerson(Person $person)
    {
        $ldapPerson = $this->buildLdapPerson($person);
        $this->ldapClient->delete($ldapPerson['dn']);
    }

    /**
     * Build an LDAP entry for a Person.
     *
     * @param Person $person The Person to build an LDAP entry for.
     *
     * @return array Array containing an LDAP dn and entry for a Person.
     */
    protected function buildLdapPerson(Person $person)
    {
        $uid = $person->getAccount()->getUserId();
        $ldapPerson = array(
            'dn' => 'uid=' . $uid . ',' . $this->peopleOu,
            'entry' => array(
                'uid' => $uid,
                'cn' => $person->getFirstName() . ' ' . $person->getLastName(),
                'objectClass' => array(
                    'top',
                    'person',
                    'organizationalPerson',
                    'inetOrgPerson'
                ),
            ),
        );

        if ($person->getAccount()->getPasswordEntity()->getClearTextPassword() != null) {
            $ldapPerson['entry']['userPassword'] = $person->getAccount()->getPasswordEntity()->getClearTextPassword();
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        $attributeMap = array(
            'id' => 'employeeNumber',
            'firstName' => 'givenName',
            'lastName' => 'sn',
            'emailAddress' => 'mail',
            'phoneNumber' => 'telephoneNumber',
            'organization' => 'o',
            'position' => 'title',
        );

        foreach ($attributeMap as $personAttribute => $ldapAttribute) {
            if ($accessor->getValue($person, $personAttribute) !== null) {
                $ldapPerson['entry'][$ldapAttribute] = $accessor->getValue($person, $personAttribute);
            }
        }

        if ($person->getAccount()->isPosix()) {
            $ldapPerson['entry']['objectClass'][] = 'posixAccount';
            $posixAttributes = array('uidNumber', 'gidNumber', 'homeDirectory', 'loginShell');
            foreach ($posixAttributes as $attribute) {
                if ($accessor->getValue($person->getAccount(), $attribute) !== null) {
                    $ldapPerson['entry'][$attribute] = $accessor->getValue($person->getAccount(), $attribute);
                }
            }
            if (count($accessor->getValue($person->getAccount(), 'sshPublicKeys')) > 0) {
                $ldapPerson['entry']['objectClass'][] = 'ldapPublicKey';
                $ldapPerson['entry']['sshPublicKey'] = array_values(
                    $accessor->getValue($person->getAccount(), 'sshPublicKeys')
                );
            }
        }

        return $ldapPerson;
    }

    /**
     * Determine if an UidNumber is already in use.
     *
     * @param string|null $uidNumber User's Uid number.
     * @param string|null $uid       Optional username to ignore associated uid Number of.
     *
     * @return boolean True if uidNumber is available, False if not.
     */
    protected function checkIfUidNumberAvailable(?string $uidNumber, $uid = null)
    {
        // If not being set, then is available, so return true.
        if (null === $uidNumber) {
            return true;
        }

        // If we pass a userId, ignore its own uidNumber.
        if (null === $uid) {
            $query = "uidNumber=$uidNumber";
        } else {
            $query = "(&(uidNumber=$uidNumber)(!(uid=$uid)))";
        }
        $entries = $this->ldapClient->find($this->peopleOu, $query);
        return null === $entries;
    }
}

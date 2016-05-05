<?php

namespace Pelagos\Component\Ldap;

use Pelagos\Entity\Person;

use Symfony\Component\PropertyAccess\PropertyAccess;

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
     * Constructor.
     *
     * @param LdapClient $ldapClient An instance of the LDAP client.
     * @param string     $ldapBindDn The distinguished name to bind with.
     * @param string     $ldapBindPw The password to bind with.
     * @param string     $peopleOu   The OU which contains people.
     */
    public function __construct(LdapClient $ldapClient, $ldapBindDn, $ldapBindPw, $peopleOu)
    {
        $this->ldapClient = $ldapClient;
        $this->peopleOu = $peopleOu;
        $this->ldapClient->bind($ldapBindDn, $ldapBindPw);
    }

    /**
     * Search for a person in LDAP.
     *
     * @param string $query The LDAP query filter.
     *
     * @return array|null
     */
    public function searchPerson($query)
    {
        $entries = $this->ldapClient->find($this->peopleOu, $query);
        return $entries;
    }

    /**
     * Add a Person to LDAP.
     *
     * @param Person $person The Person to add to LDAP.
     *
     * @return void
     */
    public function addPerson(Person $person)
    {
        $ldapPerson = $this->buildLdapPerson($person);
        $this->ldapClient->add($ldapPerson['dn'], $ldapPerson['entry']);
    }

    /**
     * Update a Person in LDAP.
     *
     * @param Person $person The Person to update in LDAP.
     *
     * @return void
     */
    public function updatePerson(Person $person)
    {
        $ldapPerson = $this->buildLdapPerson($person);
        $this->ldapClient->modify($ldapPerson['dn'], $ldapPerson['entry']);
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
            $ldapPerson['userPassword'] = $person->getAccount()->getPasswordEntity()->getClearTextPassword();
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
}

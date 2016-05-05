<?php

namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;
use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Pelagos\Component\Ldap\Ldap;
use Pelagos\Bundle\AppBundle\Handler\EntityHandler;

/**
 * A utility class to make an account into a POSIX account.
 */
class POSIXify
{
    /**
     * The entity manager to use.
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * The pelagos LDAP component.
     *
     * @var ldap
     */
    protected $ldap;

    /**
     * The Pelagos entity handler.
     *
     * @var entityHandler;
     */
    protected $entityHandler;

    /**
     * Starting POSIX UID parameter.
     *
     * @var String
     */
    protected $posixStartingUid;

    /**
     * The POSIX group id parameter.
     *
     * @var String
     */
    protected $posixGid;

    /**
     * The homedir prefix parameter.
     *
     * @var String
     */
    protected $homedirPrefix;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager The entity manager to use in querybuilder.
     * @param LDAPInterface $ldap          The instance of the LDAPClient class.
     * @param EntityHandler $entityHandler The Pelagos entity handler to handle updates.
     */
    public function __construct(EntityManager $entityManager, Ldap $ldap, EntityHandler $entityHandler, $posixStartingUid, $posixGid, $homedirPrefix)
    {
        $this->entityManager = $entityManager;
        $this->ldap = $ldap;
        $this->entityHandler = $entityHandler;
        $this->posixStartingUid = $posixStartingUid;
        $this->posixGid = $posixGid;
        $this->homedirPrefix = $homedirPrefix;
    }

    /**
     * Method to turn an Account into a POSIX-enabled account.
     *
     * @param Account $account The account needing to be POSIX-enabled.
     *
     * @throws Exception In event account is already POSIX-enabled.
     *
     * @return void
     */
    public function POSIXifyAccount(Account $account)
    {
        if ($account->isPosix() == true) {
            throw new \Exception('Account is already a POSIX account.');
        }

        $uid = $this->mintUid();

        // check LDAP to make sure this UID is not already in use, throwing
        // an exception if this is the case.  This would represent a system
        // sync issue that would need to be manually resolved.

        // I'm not sure how to use the raw ldapclient....add this later.

        // Update account's POSIX attributes.
        $account->makePosix($uid, $this->posixGid, $this->homedirPrefix);
        $this->entityHandler->update($account);

        // Get Person associated with this Account.
        $accountOwnerPerson = $account->getPerson();

        // Update LDAP with this modified Account (via Person).
        $this->ldap->updatePerson($accountOwnerPerson);

        // Issue system call to daemon that creates homedir
        // The following SUID program should (1) only write to expected places,
        // (2) handle the possibility of the directory already existing.

        // $username = $account->getUsername();
        // exec("/opt/pelagos/bin/homedirmaker -d $homedirPrefix/$username");
    }

    /**
     * Mint a POSIX UID.
     *
     * @return string The next available UID.
     */
    protected function mintUid()
    {
        // Get the account with the highest POSIX UID.
        $em = $this->entityManager;
        $query = $em->createQuery("SELECT a FROM \Pelagos\Entity\Account a WHERE a.uidNumber IS NOT NULL ORDER BY a.uidNumber DESC");
        $accounts = $query->getResult();

        if (count($accounts) == 0) {
            // If this is the first POSIX UID, we start per parameters.yml configuration.
            $sequence = intval($this->posixStartingUid);
        } else {
            $highUID = intval($accounts[0]->getUidNumber());
            $sequence = (intval($highUID) + 1);
        }
        return $sequence;
    }
}

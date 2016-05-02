<?php

namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;
use Pelagos\Entity\Account;
use Pelagos\Entity\Person;

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
     * Constructor.
     *
     * @param EntityManager $entityManager The entity manager to use.
     */
    public function __construct(EntityManager $entityManager, $ldap, $entityHandler)
    {
        $this->entityManager = $entityManager;
        $this->ldap = $ldap;
        $this->entityHandler = $entityHandler;
    }

    public function POSIXifyAccount(Account $account)
    {
        if($account->isPosix() == true) {
            throw Exception('Account is already a POSIX account.');
        }

        $uid = $this->mintUid();

        // check LDAP to make sure this UID is not already in use, throwing
        // an exception if this is the case.  This would represent a system
        // sync issue that would need to be manually resolved.

        // I'm not sure how to use the raw ldapclient....add this later.

        $gid = $this->getContainer()->getParameter('posix_gid');
        $homedirPrefix = $this->getContainer()->getParameter('homedir_prefix');

        // Update account's POSIX attributes.
        $account->makePosix($uid, $gid, $homedir_prefix);
        $this->entityHandler->update($account);

        // Get Person associated with this Account.
        $accountOwnerPerson = $account->getPerson();

        // Update LDAP with this modified Account (via Person).
        $this->$ldap->updatePerson($accountOwnerPerson);

        $username = $account->getUsername();

        // Issue system call to daemon that creates homedir
        // The following SUID program should (1) only write to expected places,
        // (2) handle the possibility of the directory already existing.

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
        $query = $this->entityManager->getRepository(Account::class)->createQueryBuilder('a')
            ->orderBy('a.uidNumber', 'DESC')
            ->getQuery();

        $accounts = $query->getResult();

        $startingUid = $this->getContainer()->getParameter('posix_starting_uid');
        if (count($accounts) == 0) {
            // If this is the first POSIX UID, we start per parameters.yml configuration.
            $sequence = $starting_uid;
        } else {
            $highUID = $accounts[0]->getUidNumber();
            $sequence = $highUID+1;
        }
        return $sequence;
    }
}

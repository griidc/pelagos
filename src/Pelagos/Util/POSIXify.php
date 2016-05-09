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
     * @var string
     */
    protected $posixStartingUid;

    /**
     * The POSIX group id parameter.
     *
     * @var string
     */
    protected $posixGid;

    /**
     * The homedir prefix parameter.
     *
     * @var string
     */
    protected $homedirPrefix;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager    The entity manager to use in querybuilder.
     * @param Ldap          $ldap             The instance of the LDAPClient class.
     * @param EntityHandler $entityHandler    The Pelagos entity handler to handle updates.
     * @param string        $posixStartingUid The value to start creating user ID number entries at.
     * @param string        $posixGid         The value to set group ID to.
     * @param string        $homedirPrefix    The location created homedirs start from.
     */
    public function __construct(
        EntityManager $entityManager,
        Ldap $ldap,
        EntityHandler $entityHandler,
        $posixStartingUid,
        $posixGid,
        $homedirPrefix
    ) {
        $this->entityManager = $entityManager;
        $this->ldap = $ldap;
        $this->entityHandler = $entityHandler;
        $this->posixStartingUid = $posixStartingUid;
        $this->posixGid = $posixGid;
        // This is set to a temporary value of /dev/null as a flag
        // for a later cronjob to correctly set after creating the directory.
        $this->homedirPrefix = '/dev/null';
    }

    /**
     * Method to turn an Account into a POSIX-enabled account.
     *
     * @param Account $account The account needing to be POSIX-enabled.
     *
     * @throws \Exception In event account is already POSIX-enabled.
     * @throws \Exception In event UID minted is greater than minimum UID from parameter.
     * @throws \Exception In event UID is already found in the LDAP.
     *
     * @return void
     */
    public function POSIXifyAccount(Account $account)
    {
        if ($account->isPosix() == true) {
            throw new \Exception('This account already has SFTP/GridFTP access.');
        }

        try {
            $uid = $this->mintUidNumber();
        } catch (\Exception $e) {
            throw $e;
        }

        // check LDAP to make sure this UID is not already in use, throwing
        // an exception if this is the case.  This would represent a system
        // sync issue that would need to be manually resolved.
        $LDAPResults = $this->ldap->searchPerson("uidNumber=$uid");
        if ($LDAPResults) {
            throw new \Exception("LDAP database error: The gidNumber $uid already exists in LDAP.");
        }

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
     * @throws \Exception In event UID minted is greater than minimum UID from parameter.
     *
     * @return string The next available UID.
     */
    protected function mintUidNumber()
    {
        // Get the account with the highest POSIX UID.
        $em = $this->entityManager;
        $query = $em->createQuery(
            'SELECT a FROM \Pelagos\Entity\Account a WHERE a.uidNumber IS NOT NULL ORDER BY a.uidNumber DESC'
        );
        $query->setMaxResults(1);
        $account = $query->getOneOrNullResult();

        if (null === $account) {
            // If this is the first POSIX UID, we start per parameters.yml configuration.
            $sequence = intval($this->posixStartingUid);
        } else {
            $highUID = $account->getUidNumber();
            $sequence = ($highUID + 1);
        }

        // sanity check.  Throw error if generated sequence > minimum UID number parameter.
        if ($sequence < $this->posixStartingUid) {
            throw new \Exception('Generated UID > starting UID number parameter');
        }

        return $sequence;
    }
}

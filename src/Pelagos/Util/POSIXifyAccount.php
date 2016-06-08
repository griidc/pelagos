<?php

namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;
use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Pelagos\Component\Ldap\Ldap;
use Pelagos\Bundle\AppBundle\Handler\EntityHandler;
use Pelagos\Exception\POSIXifyAccount\AccountAlreadyPOSIXEnabledException;

/**
 * A utility class to make an account into a POSIX account.
 */
class POSIXifyAccount
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
     * @var integer
     */
    protected $posixStartingUidNumber;

    /**
     * The POSIX group id parameter.
     *
     * @var integer
     */
    protected $posixGidNumber;

    /**
     * The homedir prefix parameter.
     *
     * @var string
     */
    protected $homedirPrefix;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager          The entity manager to use in querybuilder.
     * @param Ldap          $ldap                   The instance of the LDAPClient class.
     * @param EntityHandler $entityHandler          The Pelagos entity handler to handle updates.
     * @param integer       $posixStartingUidNumber The value to start creating user ID number entries at.
     * @param integer       $posixGidNumber         The value to set group ID to.
     * @param string        $homedirPrefix          Home directory prefix, from parameter.
     */
    public function __construct(
        EntityManager $entityManager,
        Ldap $ldap,
        EntityHandler $entityHandler,
        $posixStartingUidNumber,
        $posixGidNumber,
        $homedirPrefix
    ) {
        $this->entityManager = $entityManager;
        $this->ldap = $ldap;
        $this->entityHandler = $entityHandler;
        $this->posixStartingUidNumber = $posixStartingUidNumber;
        $this->posixGidNumber = $posixGidNumber;
        $this->homedirPrefix = $homedirPrefix;
    }

    /**
     * Method to turn an Account into a POSIX-enabled account.
     *
     * @param Account $account The account needing to be POSIX-enabled.
     *
     * @throws AccountAlreadyPOSIXEnabledException In event account is already POSIX-enabled.
     *
     * @return void
     */
    public function POSIXifyAccount(Account $account)
    {
        if ($account->isPosix() == true) {
            throw new AccountAlreadyPOSIXEnabledException('This account already has SFTP/GridFTP access.');
        }

        $uid = $this->mintUidNumber();

        // Update account's POSIX attributes.
        $account->makePosix($uid, $this->posixGidNumber, $this->homedirPrefix);
        $this->entityHandler->update($account);

        // Update LDAP with this modified Account (via Person).
        $this->ldap->updatePerson($account->getPerson());
    }

    /**
     * Mint a POSIX UID.
     *
     * @return integer The next available numeric userid.
     */
    protected function mintUidNumber()
    {
        // Get the account with the highest POSIX numeric UID.
        $em = $this->entityManager;
        $query = $em->createQuery(
            'SELECT a FROM \Pelagos\Entity\Account a WHERE a.uidNumber IS NOT NULL ORDER BY a.uidNumber DESC'
        );
        $query->setMaxResults(1);
        $account = $query->getOneOrNullResult();

        if (null === $account) {
            // If this is the first numeric POSIX UID, we start per parameters.yml configuration.
            $sequence = intval($this->posixStartingUidNumber);
        } else {
            $highUID = $account->getUidNumber();
            $sequence = ($highUID + 1);
        }

        return $sequence;
    }
}

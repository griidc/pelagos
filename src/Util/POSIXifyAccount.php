<?php

namespace App\Util;

use App\Entity\Account;
use App\Handler\EntityHandler;
use App\Exception\AccountAlreadyPOSIXEnabledException;
use Doctrine\ORM\EntityManagerInterface;

/**
 * A utility class to make an account into a POSIX account.
 */
class POSIXifyAccount
{
    /**
     * The entity manager to use.
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

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
     * @param EntityManagerInterface $entityManager          The entity manager to use in querybuilder.
     * @param EntityHandler          $entityHandler          The Pelagos entity handler to handle updates.
     * @param integer                $posixStartingUidNumber The value to start creating user ID number entries at.
     * @param integer                $posixGidNumber         The value to set group ID to.
     * @param string                 $homedirPrefix          Home directory prefix, from parameter.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        EntityHandler $entityHandler,
        int $posixStartingUidNumber,
        int $posixGidNumber,
        string $homedirPrefix
    ) {
        $this->entityManager = $entityManager;
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

        $uidNumber = $this->mintUidNumber();

        // Update account's POSIX attributes.
        $account->makePosix($uidNumber, $this->posixGidNumber, $this->homedirPrefix);

        // Persist changes.
        $this->entityHandler->update($account);
    }

    /**
     * Mint a POSIX UID.
     *
     * @return integer The next available numeric userid.
     */
    protected function mintUidNumber()
    {
        // Get the account with the highest POSIX numeric UID.
        $query = $this->entityManager->createQuery(
            'SELECT a FROM \App\Entity\Account a WHERE a.uidNumber IS NOT NULL ORDER BY a.uidNumber DESC'
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

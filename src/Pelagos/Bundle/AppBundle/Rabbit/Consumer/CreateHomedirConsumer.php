<?php

namespace Pelagos\Bundle\AppBundle\Rabbit\Consumer;

use Symfony\Component\Ldap\Exception\LdapException;

use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Pelagos\Component\Ldap\Ldap;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bridge\Monolog\Logger;

/**
 * A consumer of homerdir creation request messages.
 *
 * @see ConsumerInterface
 */
class CreateHomedirConsumer extends ConsumerInterface
{

    /**
     * A Monolog logger.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * The entity manager.
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * The prefix for home directories, from config.
     *
     * @var string
     */
    protected $homedirPrefix;

    /**
     * The pelagos LDAP component.
     *
     * @var ldap
     */
    protected $ldap;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager The entity manager.
     * @param Logger        $logger        A Monolog logger.
     * @param string        $homedirPrefix Prefix of home directories.
     * @param Ldap          $ldap          The Pelagos Ldap component.
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger,
        $homedirPrefix,
        Ldap $ldap
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->homedirPrefix = $homedirPrefix;
        $this->ldap = $ldap;
    }

   /**
    * Process a filer message.
    *
    * @param AMQPMessage $message A filer message.
    *
    * @return boolean True if success, false otherwise.
    */
    protected function execute(AMQPMessage $message)
    {
        $userId = $message->body;

       // find account by id and that is flagged with /dev/null
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $account = $queryBuilder
            ->select('account')
            ->from(Account::class, 'account')
            ->where('account.userId = :userId')
            ->andWhere(
                $queryBuilder->expr()->like('account.homeDirectory', ':pattern')
            )
            ->setParameter('pattern', '/dev/null/%')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($account) {

            // Get username for the last part of homedir.
            $username = $account->getUserName();
            $homeDir = $this->homedirPrefix . "/$username";

           // Set correct path in the model.
            $this->logger->info('Updating database for: ' . $username . '.');
            $account->setHomeDirectory($homeDir);
            $entityManager->persist($account);
            $entityManager->flush();

            // Get Person associated with this Account.
            $accountOwnerPerson = $account->getPerson();

            // Adjust LDAP definition.
            try {
                $this->ldap->updatePerson($accountOwnerPerson);
                $this->logger->info('Updating LDAP for: ' . $username . '.');
            } catch (LdapException $e) {
                $this->logger->error("Failed updating LDAP for $username.");
                return false;
            }

            // Check to see if a directory already exists.
            if (is_dir($homeDir)) {
                $this->logger->error("Directory for $username already exists.");
                return false;
            }

            $this->logger->info(
                'Creating homedir for user ' . $username . ': ' . $this->homedirPrefix . "/$username\n"
            );

            // Create home directory, owned by script-running system user (pelagos).
            if (false == mkdir("$homeDir", 0750, false)) {
                $this->logger->error("Could not create homedir: $homeDir");
                return false;
            }

            // Give Apache r-x on homedir with FACL.
            $outputLines = array();
            exec("/usr/bin/setfacl -m u:apache:r-x $homeDir", $outputLines, $returnValue);
            if ($returnValue != 0) {
                $this->logger->error("Error setting facl (u:apache:r-x) on $homeDir. $outputLines");
                return false;
            }

            // Give user r-x on homedir with FACL.
            $outputLines = array();
            exec("/usr/bin/setfacl -m u:$username:r-x $homeDir", $outputLines, $returnValue);
            if ($returnValue != 0) {
                $this->logger->error("Error setting facl (u:$username:r-x) on $homeDir. $outputLines");
                return false;
            }

            // Create incoming directory, owned by script-running system user (pelagos).
            if (false == mkdir("$homeDir/incoming", 0750, false)) {
                $this->logger->error("Could not create directory: $homeDir/incoming.");
                return false;
            }

            // Give Apache rwx on homedir/incoming with FACL.
            $outputLines = array();
            exec("/usr/bin/setfacl -m u:apache:rwx $homeDir/incoming", $outputLines, $returnValue);
            if ($returnValue != 0) {
                $this->logger->error("Error setting facl (u:apache:rwx) on $homeDir/incoming. $outputLines");
                return false;
            }

            // Give user rwx on homedir/incoming with FACL.
            exec("/usr/bin/setfacl -m u:$username:rwx $homeDir/incoming");
            if ($returnValue != 0) {
                $this->logger->error("Error setting facl (u:$username:rwx) on $homeDir/incoming. $outputLines");
                return false;
            }

            // Create download directory, owned by script-running system user (pelagos).
            if (false == mkdir("$homeDir/download", 0750, false)) {
                $this->logger->error("Could not create $homeDir/download.");
                return false;
            }

            // Give Apache rwx on homedir/download with FACL.
            $outputLines = array();
            exec("/usr/bin/setfacl -m u:apache:rwx $homeDir/download", $outputLines, $returnValue);
            if ($returnValue != 0) {
                $this->logger->error("Error setting facl (u:apache:rwx) on $homeDir/download.");
                return false;
            }

            // Give user r-x on homedir/download with FACL.
            $outputLines = array();
            exec("/usr/bin/setfacl -m u:$username:r-x $homeDir/download", $outputLines, $returnValue);
            if ($returnValue != 0) {
                $this->logger->error("Error setting facl (u:$username:r-x) on $homeDir/download.");
                return false;
            }

            // Persist changes to Account if everything has worked up to this point.
            $this->logger->info("Updating database for: $username.");
            $entityManager->persist($account);
            $entityManager->flush();
        } else {
            $this->logger->error("No account found for $userId");
            return false;
        }
        return true;
    }
}

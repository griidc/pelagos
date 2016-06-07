<?php

namespace Pelagos\Bundle\AppBundle\Rabbit\Consumer;

use Symfony\Component\Ldap\Exception\LdapException;

use Pelagos\Entity\Account;
use Pelagos\Entity\Person;
use Pelagos\Component\Ldap\Ldap;

use Doctrine\ORM\EntityManager;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bridge\Monolog\Logger;

/**
 * A consumer of homerdir creation request messages.
 *
 * @see ConsumerInterface
 */
class CreateHomedirConsumer implements ConsumerInterface
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
     * @param Ldap          $ldap          The Pelagos Ldap component.
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger,
        Ldap $ldap
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->ldap = $ldap;
    }

   /**
    * Process a filer message.
    *
    * @param AMQPMessage $message A filer message.
    *
    * @return boolean True if success, false otherwise.
    */
    public function execute(AMQPMessage $message)
    {
        $account = $this->entityManager->find(Account::class, $message->body);

        if ($account instanceof Account) {

            // Get username, homedir.
            $username = $account->getUserName();
            $homeDir = $account->getHomeDirectory();

            // Get Person associated with this Account.
            $accountOwnerPerson = $account->getPerson();

            // Adjust LDAP definition.
            try {
                $this->ldap->updatePerson($accountOwnerPerson);
                $this->logger->info('Updating LDAP for: ' . $username . '.');
            } catch (LdapException $e) {
                $this->logger->error("Failed updating LDAP for $username.");
                // Returning true on consumer failure prevents re-execution of message.  (Must log error or throw event.)
                return true;
            }

            // Check to see if a directory already exists.
            if (is_dir($homeDir)) {
                $this->logger->error("Directory for $username already exists.");
                return true;
            }

            $this->logger->info(
                "Creating homedir for user $username: $homeDir"
            );

            // Create home directory, owned by script-running system user (pelagos).
            if (false == mkdir("$homeDir", 0750, false)) {
                $this->logger->error("Could not create homedir: $homeDir");
                return true;
            }

            // Give Apache r-x on homedir with FACL.
            $outputLines = array();
            exec("/usr/bin/setfacl -m u:apache:r-x $homeDir", $outputLines, $returnValue);
            if ($returnValue != 0) {
                $this->logger->error("Error setting facl (u:apache:r-x) on $homeDir. $outputLines");
                return true;
            }

            // Give user r-x on homedir with FACL.
            $outputLines = array();
            exec("/usr/bin/setfacl -m u:$username:r-x $homeDir", $outputLines, $returnValue);
            if ($returnValue != 0) {
                $this->logger->error("Error setting facl (u:$username:r-x) on $homeDir. $outputLines");
                return true;
            }

            // Create incoming directory, owned by script-running system user (pelagos).
            if (false == mkdir("$homeDir/incoming", 0750, false)) {
                $this->logger->error("Could not create directory: $homeDir/incoming.");
                return true;
            }

            // Give Apache rwx on homedir/incoming with FACL.
            $outputLines = array();
            exec("/usr/bin/setfacl -m u:apache:rwx $homeDir/incoming", $outputLines, $returnValue);
            if ($returnValue != 0) {
                $this->logger->error("Error setting facl (u:apache:rwx) on $homeDir/incoming. $outputLines");
                return true;
            }

            // Give user rwx on homedir/incoming with FACL.
            exec("/usr/bin/setfacl -m u:$username:rwx $homeDir/incoming");
            if ($returnValue != 0) {
                $this->logger->error("Error setting facl (u:$username:rwx) on $homeDir/incoming. $outputLines");
                return true;
            }

            // Create download directory, owned by script-running system user (pelagos).
            if (false == mkdir("$homeDir/download", 0750, false)) {
                $this->logger->error("Could not create $homeDir/download.");
                return true;
            }

            // Give Apache rwx on homedir/download with FACL.
            $outputLines = array();
            exec("/usr/bin/setfacl -m u:apache:rwx $homeDir/download", $outputLines, $returnValue);
            if ($returnValue != 0) {
                $this->logger->error("Error setting facl (u:apache:rwx) on $homeDir/download.");
                return true;
            }

            // Give user r-x on homedir/download with FACL.
            $outputLines = array();
            exec("/usr/bin/setfacl -m u:$username:r-x $homeDir/download", $outputLines, $returnValue);
            if ($returnValue != 0) {
                $this->logger->error("Error setting facl (u:$username:r-x) on $homeDir/download.");
                return true;
            }
        } else {
            $this->logger->error("No account found for Account Entity id# $message->body");
            return true;
        }
        return true;
    }
}

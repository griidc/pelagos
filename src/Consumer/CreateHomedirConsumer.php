<?php

namespace Pelagos\Bundle\AppBundle\Rabbit\Consumer;

use Symfony\Component\Ldap\Exception\LdapException;

use Pelagos\Entity\Account;
use Pelagos\Entity\Person;

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
     * Constructor.
     *
     * @param EntityManager $entityManager The entity manager.
     * @param Logger        $logger        A Monolog logger.
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

   /**
    * Process a create_homedir message.
    *
    * @param AMQPMessage $message A create_homedir message.
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

            // Check to see if a directory already exists.
            if (is_dir($homeDir)) {
                $this->logger->warn("Directory for $username already exists.");
            } else {
                // Create home directory, owned by script-running system user (pelagos).
                if (false == mkdir("$homeDir", 0750, false)) {
                    $this->logger->error("Could not create homedir: $homeDir");
                    return true;
                } else {
                    $this->logger->info("Creating $homeDir.");
                }
            }
            $this->setLinuxAcl('apache', $homeDir, 'r-x');
            $this->setLinuxAcl($username, $homeDir, 'r-x');

            // Create incoming directory, owned by script-running system user (pelagos).
            if (is_dir("$homeDir/incoming")) {
                $this->logger->warn("$homeDir/incoming already exists.");
            } else {
                if (false == mkdir("$homeDir/incoming", 0750, false)) {
                    $this->logger->error("Could not create directory: $homeDir/incoming.");
                    return true;
                } else {
                    $this->logger->info("Creating $homeDir/incoming.");
                }
            }
            $this->setLinuxAcl('apache', "$homeDir/incoming", 'rwx');
            $this->setLinuxAcl($username, "$homeDir/incoming", 'rwx');

            // Create download directory, owned by script-running system user (pelagos).
            if (is_dir("$homeDir/download")) {
                $this->logger->warn("$homeDir/download already exists.");
            } else {
                if (false == mkdir("$homeDir/download", 0750, false)) {
                    $this->logger->error("Could not create $homeDir/download.");
                    return true;
                } else {
                    $this->logger->info("Creating $homeDir/download.");
                }
            }
            $this->setLinuxAcl('apache', "$homeDir/download", 'rwx');
            $this->setLinuxAcl($username, "$homeDir/download", 'r-x');
        } else {
            $this->logger->error("No account found for Account Entity id# $message->body");
        }
        return true;
    }

   /**
    * Sets Filesystem Access Control List.
    *
    * @param string $user Linux username to set ACL for.
    * @param string $path Path of directory or file to set ACL on.
    * @param string $acl  Actual rwx string to use in setting ACL.
    *
    * @return Boolean True on success, false on failure.
    */
    protected function setLinuxAcl($user, $path, $acl)
    {
        $outputLines = array();
        $status = true;
        exec("/usr/bin/setfacl -m u:$user:$acl $path", $outputLines, $returnValue);
        if ($returnValue != 0) {
            $this->logger->error("Error setting facl (u:$user:$acl) on $path.");
            $status = false;
        } else {
            $this->logger->info("ACL Configuration: Set $acl on $path for $user.");
        }
        return $status;
    }
}

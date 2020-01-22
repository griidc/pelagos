<?php

namespace App\Consumer;

use App\Entity\Account;

use Doctrine\ORM\EntityManagerInterface;

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
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * The Filesystem type, NFS or Linux.
     *
     * @var string A string of 'Linux' or 'NFS'
     */
    protected $filesystemType;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager  The entity manager.
     * @param Logger                 $logger         A Monolog logger.
     * @param string                 $filesystemType Filesystem type, NFS or Linux.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Logger $logger,
        string $filesystemType
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->filesystemType = $filesystemType;
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
        if ('Linux' === $this->filesystemType) {
            return($this->processNormalFS($message));
        } else {
            return($this->processNFS($message));
        }
    }

   /**
    * Sets Filesystem Access Control List for NFSv4 systems.
    *
    * @param string $user Linux username to set ACL for.
    * @param string $path Path of directory or file to set ACL on.
    * @param string $acl  Actual rwx string to use in setting ACL.
    *
    * @return boolean True on success, false on failure.
    */
    protected function setLinuxNfs4UserAcl(string $user, string $path, string $acl)
    {
        $outputLines = array();
        $status = true;
        $id = posix_getpwnam($user);
        exec("/usr/bin/nfs4_setfacl -a A::$id:$acl $path", $outputLines, $returnValue);
        if ($returnValue != 0) {
            $this->logger->error("Error setting nfs4_facl (A::$id:$acl) on $path. (user: $user)");
            $status = false;
        } else {
            $this->logger->info("NFS4 ACL Configuration: Added A::$id:$acl on $path for $id (user: $user).");
        }
        return $status;
    }

   /**
    * Sets Filesystem Access Control List on traditional Linux filesystems.
    *
    * @param string $user Linux username to set ACL for.
    * @param string $path Path of directory or file to set ACL on.
    * @param string $acl  Actual rwx string to use in setting ACL.
    *
    * @return boolean True on success, false on failure.
    */
    protected function setLinuxAcl(string $user, string $path, string $acl)
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

    /**
     * This is the new, NFSv4 way of handling ACLs, as used by the NetApp or other NFSv4 systems.
     *
     * @param mixed $message A RabbitMQ message.
     *
     * @return boolean True on success, false on failure.
     */
    private function processNFS($message)
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
            $this->setLinuxNfs4UserAcl('apache', $homeDir, 'RX');
            $this->setLinuxNfs4UserAcl($username, $homeDir, 'RX');

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
            $this->setLinuxNfs4UserAcl('apache', "$homeDir/incoming", 'RWX');
            $this->setLinuxNfs4UserAcl($username, "$homeDir/incoming", 'RWX');

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
            $this->setLinuxNfs4UserAcl('apache', "$homeDir/download", 'RWX');
            $this->setLinuxNfs4UserAcl($username, "$homeDir/download", 'RX');
        } else {
            $this->logger->error("No account found for Account Entity id# $message->body");
        }
        return true;
    }

    /**
     * This is the old standard linux Fileystem ACL way of doing things.
     *
     * @param mixed $message A rabbitMQ message.
     *
     * @return boolean True on success, false on failure.
     */
    private function processNormalFS($message)
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
}

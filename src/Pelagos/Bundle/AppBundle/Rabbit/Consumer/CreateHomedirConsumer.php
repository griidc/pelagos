<?php

namespace Pelagos\Bundle\AppBundle\Rabbit\Consumer;

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

            // adjust LDAP definition
            // Get Person associated with this Account.
            $accountOwnerPerson = $account->getPerson();

            // Update LDAP with this modified Account (via Person).
            $this->logger->info('Updating LDAP for: ' . $username . '.');
            $this->ldap->updatePerson($accountOwnerPerson);

            // Create home directory on server.
            if (is_dir($homeDir)) {
                $this->logger->error("Directory for $username already exists.");
                return false;
            }

            $this->logger->info(
                'Creating homedir for user ' . $username . ': ' . $this->homedirPrefix . "/$username\n"
            );

            // Create home directory.
            mkdir("$homeDir", 0750, false);
            exec("/usr/bin/setfacl -m u:apache:r-x $homeDir");
            exec("/usr/bin/setfacl -m u:$username:r-x $homeDir");
            // create incoming directory.
            mkdir("$homeDir/incoming", 0750, false);
            exec("/usr/bin/setfacl -m u:apache:rwx $homeDir/incoming");
            exec("/usr/bin/setfacl -m u:$username:rwx $homeDir/incoming");
            // Create download directory
            mkdir("$homeDir/download", 0750, false);
            exec("/usr/bin/setfacl -m u:apache:rwx $homeDir/download");
            exec("/usr/bin/setfacl -m u:$username:r-x $homeDir/download");
        } else {
            $this->logger->error("No account found for $userId");
            return false;
        }
        return true;
    }
}

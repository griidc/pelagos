<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Account;
use Pelagos\Entity\Person;

/**
 * Create homedir structure for specified POSIX user.
 *
 * @see ContainerAwareCommand
 */
class CreateHomedirCommand extends ContainerAwareCommand
{
    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('account:create-homedir')
            ->setDescription('Create homedir structure for POSIX users with homedir placeholder of /dev/null/username');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An Input Interface instance we don't use.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \Exception If a directory to be created already exists.
     * @throws \Exception If a non-account is encountered.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // not used
        unset($input);

        // get entitymanager
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        // get homedir prefix set in paramaters.yml
        $prefix = $this->getContainer()->getParameter('homedir_prefix');

        // find accounts flagged with /dev/null
        $queryBuilder = $entityManager->createQueryBuilder();
        $result = $queryBuilder
            ->select('account')
            ->from(Account::class, 'account')
            ->where(
                $queryBuilder->expr()->like('account.homeDirectory', ':pattern')
            )
            ->setParameter('pattern', '/dev/null/%')
            ->getQuery()
            ->getResult();

        $accounts = $result;
        foreach ($accounts as $account) {
            if ($account instanceof Account) {

                // Get username for the last part of homedir.
                $username = $account->getUserName();
                $homeDir = "$prefix/$username";

                // Set correct path in the model.
                $output->writeln('Updating database for: ' . $username . '.');
                $account->setHomeDirectory($homeDir);
                $entityManager->persist($account);
                $entityManager->flush();

                // adjust LDAP definition
                // Get Person associated with this Account.
                $accountOwnerPerson = $account->getPerson();

                // Update LDAP with this modified Account (via Person).
                $output->writeln('Updating LDAP for: ' . $username . '.');
                $this->getContainer()->get('pelagos.ldap')->updatePerson($accountOwnerPerson);

                // Create home directory on server.
                if (is_dir($homeDir)) {
                    throw new \Exception("Directory for $username already exists.");
                }
                $output->writeln('Creating homedir for user ' . $username . ': ' . "$prefix/$username\n");
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
                throw new \Exception('Expected account, got something else.');
            }
        }
        return 0;
    }
}

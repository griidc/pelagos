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
     * @param OutputInterface $output An OutputInterface instance.
     * @param InputInterface $input An Input Interface instance we don't use.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // not used
        unset($input);

        // get entity handler
        $entityHandler = $this->getContainer()->get('pelagos.entity.handler');

        // get homedir prefix set in paramaters.yml
        $prefix = $this->getContainer()->getParameter('homedir_prefix');

        // find accounts flagged with /dev/null
        //$queryBuilder = $this->getContainer()->get('doctrine.orm.entity_manager')->createQueryBuilder();
        $queryBuilder = $entityHandler->getEntityManager()->createQueryBuilder();
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
                $entityHandler->update($account);

                // adjust LDAP definition
                // Get Person associated with this Account.
                $accountOwnerPerson = $account->getPerson();

                // Update LDAP with this modified Account (via Person).
                //$output->writeln('Updating LDAP for: ' . $username . '.');
                //$this->getContainer()->get('pelagos.ldap')->updatePerson($accountOwnerPerson);

                // Create home directory on server.
                $output->writeln('Creating homedir for user: ' . $username . 'as' . "$prefix/$username" . '.');
                mkdir("$homeDir/incoming", 0711, true);
            } else {
                throw new \Exception('Expected account, got something else.');
            }
        // Return success.
        return 0;
        }
    }
}

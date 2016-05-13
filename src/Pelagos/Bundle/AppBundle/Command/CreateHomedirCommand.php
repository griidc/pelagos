<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Ldap\Exception\LdapException;

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
                $queryBuilder->expr()->eq('account.posix', $queryBuilder->expr()->literal(true))
            )
            ->andWhere(
                $queryBuilder->expr()->like('account.homeDirectory', ':pattern')
            )
            ->setParameter('pattern', '/dev/null/%')
            ->getQuery()
            ->getResult();

        $totalErrors = 0;
        $accounts = $result;
        foreach ($accounts as $account) {

            $errorString = '';
            $errorCount = 0;

            if ($account instanceof Account) {

                // Get username for the last part of homedir.
                $username = $account->getUserName();
                $homeDir = "$prefix/$username";

                // Set correct path in the model.
                $account->setHomeDirectory($homeDir);

                // adjust LDAP definition
                $accountOwnerPerson = $account->getPerson();
                try {
                    $this->getContainer()->get('pelagos.ldap')->updatePerson($accountOwnerPerson);
                    $output->writeln("Updating LDAP for $username.");
                } catch (LdapException $e) {
                    $errorCount++;
                    $errorString .= "Failed updating LDAP for $username.";
                }

                // Check to see if a directory already exists.
                if (is_dir($homeDir)) {
                    $errorCount++;
                    $errorString .= "Homedir already exists: $homeDir.\n";
                }

                $output->writeln("Creating homedir structure for user: $homeDir\n");

                // Create home directory, owned by script-running system user (pelagos).
                if (false == mkdir("$homeDir", 0750, false)) {
                    $errorCount++;
                    $errorString .= "Could not create homedir: $homeDir.\n";
                }

                // Give Apache r-x on homedir with FACL.
                $outputLines = array();
                exec("/usr/bin/setfacl -m u:apache:r-x $homeDir", $outputLines, $returnValue);
                if ($returnValue != 0) {
                    $errorCount++;
                    $errorString .= "Error setting facl (u:apache:r-x) on $homeDir.\n";
                }

                // Give user r-x on homedir with FACL.
                $outputLines = array();
                exec("/usr/bin/setfacl -m u:$username:r-x $homeDir", $outputLines, $returnValue);
                if ($returnValue != 0) {
                    $errorCount++;
                    $errorString .= "Error setting facl (u:$username:r-x) on $homeDir.\n";
                }

                // Create incoming directory, owned by script-running system user (pelagos).
                if (false == mkdir("$homeDir/incoming", 0750, false)) {
                    $errorCount++;
                    $errorString .= "Could not create directory: $homeDir/incoming.\n";
                }

                // Give Apache rwx on homedir/incoming with FACL.
                $outputLines = array();
                exec("/usr/bin/setfacl -m u:apache:rwx $homeDir/incoming", $outputLines, $returnValue);
                if ($returnValue != 0) {
                    $errorCount++;
                    $errorString .= "Error setting facl (u:apache:rwx) on $homeDir/incoming.\n";
                }

                // Give user rwx on homedir/incoming with FACL.
                exec("/usr/bin/setfacl -m u:$username:rwx $homeDir/incoming");
                if ($returnValue != 0) {
                    $errorCount++;
                    $errorString .= "Error setting facl (u:$username:rwx) on $homeDir/incoming.\n";
                }

                // Create download directory, owned by script-running system user (pelagos).
                if (false == mkdir("$homeDir/download", 0750, false)) {
                    $errorCount++;
                    $errorString .= "Could not create $homeDir/download.\n";
                }

                // Give Apache rwx on homedir/download with FACL.
                $outputLines = array();
                exec("/usr/bin/setfacl -m u:apache:rwx $homeDir/download", $outputLines, $returnValue);
                if ($returnValue != 0) {
                    $errorCount++;
                    $errorString .= "Error setting facl (u:apache:rwx) on $homeDir/download.\n";
                }

                // Give user r-x on homedir/download with FACL.
                $outputLines = array();
                exec("/usr/bin/setfacl -m u:$username:r-x $homeDir/download", $outputLines, $returnValue);
                if ($returnValue != 0) {
                    $errorCount++;
                    $errorString .= "Error setting facl (u:$username:r-x) on $homeDir/download.\n";
                }

                // Persist changes to Account if everything has worked up to this point.
                if (0 == $errorCount) {
                    $output->writeln('Updating database for: ' . $username . '.');
                    $entityManager->persist($account);
                    $entityManager->flush();
                } else {
                    echo $errorString;
                }
            } else {
                    $output->writeln("Encountered unexpected non-Account object.\n");
            }
            $totalErrors += $errorCount;
        }
        // Exit with error code 1 if there were any errors.
        return ($totalErrors != 0);
    }
}

<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Pelagos\Entity\Account;
use Pelagos\Entity\Password;
use Pelagos\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command that removes an account by username, and the associated password and LDAP entries.
 */
class RemoveAccountCommand extends ContainerAwareCommand
{
    /**
     * Configuration for this command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('account:remove')
            ->setDescription('Fully removes account by username, passwords, and LDAP entry.')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'What is the username of the account you wish to delete?'
            );
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input  The Symfony console input service.
     * @param OutputInterface $output The Symfony console output service.
     *
     * @throws \Exception If Account could not be located.
     * @throws \Exception If Reflection fails.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $username = $input->getArgument('username');

        $accounts = $entityManager->getRepository(Account::class)->findBy(array('userId' => $username));
        if (count($accounts) < 1) {
            throw new \Exception('ERROR: Account could not be located for ' . $username . '.');
        }
        $account = $accounts[0];

        // Attempt to remove from LDAP, give warning if cannot, but continue with userdel.
        try {
            $output->writeln("Attempting to remove LDAP account for $username.");
            $ldap = $this->getcontainer()->get('pelagos.ldap');
            $ldap->deletePerson($account->getPerson());
        } catch (\Exception $exception) {
            $output->writeln("WARNING: Could not remove $username from LDAP. - " . $exception->getMessage());
        }

        $passwords = $entityManager->getRepository(Password::class)->findBy(array('account' => $account));

        // Remove reference in Account to Password by nulling via Reflections. Because of a circular reference,
        // Reflections is used to break in.
        $accountReflection = new \ReflectionClass(Account::class);
        $accountPasswordReflection = $accountReflection->getproperty('password');
        $accountPasswordReflection->setAccessible(true);
        $accountPasswordReflection->setValue($account, null);
        $entityManager->persist($account);

        // Remove references in Password to Account, also via Reflections.
        $output->writeln('Removing ' . count($passwords) .
            ' passwords associated with ' . $username . '.');
        foreach ($passwords as $password) {
            try {
                $passwordReflection = new \ReflectionClass(Password::class);
                $passwordAccountReflection = $passwordReflection->getProperty('account');
                $passwordAccountReflection->setAccessible(true);
                $passwordAccountReflection->setValue($password, null);
                $entityManager->persist($password);
            } catch (\ReflectionException $exception) {
                throw new \Exception('Reflection class failed ' . $exception->getMessage());
            }
        }
        $entityManager->flush();

        // Remove the Password entities.
        foreach ($passwords as $password) {
            $entityManager->remove($password);
        }


        $entityManager->remove($account);
        $entityManager->flush();
    }
}

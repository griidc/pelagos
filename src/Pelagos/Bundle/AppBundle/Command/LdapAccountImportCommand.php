<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Account;
use Pelagos\Entity\Password;
use Pelagos\Entity\Person;

/**
 * A command that imports accounts from LDAP.
 */
class LdapAccountImportCommand extends ContainerAwareCommand
{
    /**
     * Configuration for this command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('ldap:import-accounts')
            ->setDescription('Import accounts from LDAP');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input  The Symfony console input service.
     * @param OutputInterface $output The Symfony console output service.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        unset($input);

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $ldapClient = $this->getContainer()->get('pelagos.ldap_client');

        $ldapBindDn = $this->getContainer()->getParameter('ldap_bind_dn');
        $ldapBindPw = $this->getContainer()->getParameter('ldap_bind_pw');

        $ldapClient->bind($ldapBindDn, $ldapBindPw);

        $peopleOu = $this->getContainer()->getParameter('ldap_people_ou');

        $people = $ldapClient->find($peopleOu, 'objectClass=person');

        $passwordReflection = new \ReflectionClass(Password::class);
        $hashAlgorithmReflection = $passwordReflection->getProperty('passwordHashAlgorithm');
        $hashAlgorithmReflection->setAccessible(true);
        $hashReflection = $passwordReflection->getProperty('passwordHash');
        $hashReflection->setAccessible(true);
        $saltReflection = $passwordReflection->getProperty('passwordHashSalt');
        $saltReflection->setAccessible(true);

        $accountReflection = new \ReflectionClass(Account::class);
        $homeDirectoryReflection = $accountReflection->getProperty('homeDirectory');
        $homeDirectoryReflection->setAccessible(true);

        $systemPerson = $entityManager->find(Person::class, 0);

        foreach ($people as $ldapPerson) {
            $mail = $ldapPerson['mail'][0];
            $people = $entityManager->getRepository(Person::class)->findBy(array('emailAddress' => strtolower($mail)));
            if (count($people) === 0) {
                $output->writeln($ldapPerson['uid'][0] . ' - no matching person found for ' . $mail);
                continue;
            }

            $person = $people[0];
            if ($person->getAccount() instanceof Account) {
                $output->writeln($ldapPerson['uid'][0] . ' - already has an account');
                continue;
            }

            if (!preg_match('/^\{([^\}]+)\}(.*)$/', $ldapPerson['userpassword'][0], $matches)) {
                $output->writeln($ldapPerson['uid'][0] . ' - could not decode password hash');
                continue;
            }

            list($all, $hashAlgorithm, $hashAndSalt) = $matches;
            $decodedHashAndSalt = base64_decode($hashAndSalt);
            $hash = substr($decodedHashAndSalt, 0, -4);
            $salt = substr($decodedHashAndSalt, -4);
            $password = new Password('Temporary!');
            $password->setCreator($systemPerson);
            $hashAlgorithmReflection->setValue($password, $hashAlgorithm);
            $hashReflection->setValue($password, $hash);
            $saltReflection->setValue($password, $salt);

            $account = new Account($person, $ldapPerson['uid'][0], $password);
            $account->setCreator($systemPerson);

            if (in_array('posixAccount', $ldapPerson['objectclass'])) {
                $account->makePosix(
                    intval($ldapPerson['uidnumber'][0]),
                    intval($ldapPerson['gidnumber'][0]),
                    'temporary',
                    $ldapPerson['loginshell'][0]
                );
                $homeDirectoryReflection->setValue($account, $ldapPerson['homedirectory'][0]);
            }

            if (in_array('ldapPublicKey', $ldapPerson['objectclass'])) {
                foreach ($ldapPerson['sshpublickey'] as $key) {
                    if (preg_match('/^\S+\s+\S+\s+(.*)$/', $key, $matches)) {
                        list($all, $comment) = $matches;
                        $account->addSshPublicKey($key, $comment);
                    }
                }
            }

            $entityManager->persist($account);

            $output->writeln($ldapPerson['uid'][0] . ' - account imported');
        }
        $entityManager->flush();
    }
}

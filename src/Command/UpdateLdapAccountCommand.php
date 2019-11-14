<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Ldap\Exception\LdapException;

use App\Entity\Account;
use App\Entity\Password;
use App\Entity\Person;
use App\Util\Ldap\Ldap;

/**
 * Sync LDAP with the database account.
 *
 * @see Command
 */
class UpdateLdapAccountCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:update-ldap-account';

    /**
     * Ldap Utility instance.
     *
     * @var Ldap
     */
    protected $ldap;

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Class constructor for dependency injection.
     *
     * @param Ldap                   $ldap          LDAP utility.
     * @param EntityManagerInterface $entityManager An instance of entity manager.
     */
    public function __construct(
        Ldap $ldap,
        EntityManagerInterface $entityManager
    ) {
        $this->ldap = $ldap;
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Sync database user account with LDAP user account')
            ->addOption(
                'password',
                'p',
                InputOption::VALUE_NONE,
                'Update password.'
            )
            ->addOption(
                'flush',
                'f',
                InputOption::VALUE_NONE,
                'Also flush password change to database.'
            );
        ;
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \RuntimeException When a username is not supplied.
     *                           When more than one user is found.
     *                           When User or Person are not the correct instance.
     *                           When the password is not set correctly.
     *                           When the password is empty.
     *
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $flush = $input->getOption('flush');
        $updatePassword = $input->getOption('password');

        $io->title('LDAP Re-Syncing Tool');
        $io->text([
            'This command will sync the LDAP account,',
            'and set the password if requested.'
        ]);

        $username = $io->ask('Username to sync', null, function ($username) {
            if (empty($username)) {
                throw new \RuntimeException('Please supply a username!');
            }

            return $username;
        });

        $io->text('Looking up account.');

        $users = $this->entityManager->getRepository(Account::class)->findBy(
            array('userId' => $username)
        );

        if (empty($users)) {
            $io->error('User not found');
            return false;
        } elseif (count($users) > 1) {
            throw new \RuntimeException('More than one user found!');
        }

        $user = $users[0];
        if (!$user instanceof Account) {
            throw new \RuntimeException('User is not an instance of Account!');
        }

        if ($updatePassword) {
            $password = $io->askHidden('What is your password?', function ($password) {
                if (empty($password)) {
                    throw new \RuntimeException('Password cannot be empty.');
                }

                $password = new Password($password);

                return $password;
            });
            $io->text('Setting Password');
            $passwordsMatch = $user->getPasswordEntity()->comparePassword($password->getClearTextPassword());
            if (!$flush and !$passwordsMatch) {
                $io->caution('Passwords must match without Flush option, password not set.');
            } else {
                $user->setPassword($password, true);
            }
        }

        $person = $user->getPerson();
        if (!$person instanceof Person) {
            throw new \RuntimeException('Person is not an instance of Person!');
        }

        if (!$io->confirm('Are you sure?', false)) {
            $io->warning('Command aborted');
            return 0;
        }

        $io->text('Updating LDAP');

        try {
            $this->ldap->updatePerson($person);
        } catch (LdapException $e) {
            $io->error('LDAP Object does not exist!');
            return 0;
        }

        if ($flush) {
            $io->text('Flushing to database');
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $io->success(sprintf('LDAP account for user %s has been updated.', $username));

        return 0;
    }
}

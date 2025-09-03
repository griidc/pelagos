<?php

namespace App\Command;

use App\Util\LdapClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'pelagos:ldap-update',
    description: 'Update LDAP',
)]
class PelagosLdapUpdateCommand extends Command
{
    public function __construct(private LdapClient $ldapClient)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            // ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        // $arg1 = $input->getArgument('arg1');

        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }

        // if ($input->getOption('option1')) {
        //     // ...
        // }

        $this->ldapClient->bind('cn=admin,dc=griidc,dc=org', 'password');


        $this->ldapClient->add('cn=Mickel,ou=people,dc=griidc,dc=org', [
            'objectClass' => ['organizationalUnit'],
            'sn' => ['fabpot'],
        ]);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}

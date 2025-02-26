<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Account;

/**
 * Command to display a user's active/inactive status.
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:CheckUser', description: 'Check status of a user by username.')]
class CheckUserCommand extends Command
{
    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        // It is required to call parent constructor if
        // using a constructon in a Symfony command.
        parent::__construct();
    }

    /**
     * The command configuration.
     *
     * @return void
     */
    protected function configure()
    {
        $this->
            addArgument('username', InputArgument::REQUIRED, 'Username to be checked');
    }

    /**
     * Symfony command execute section.
     *
     * @param InputInterface  $input  Required by Command.
     * @param OutputInterface $output Required by Command.
     *
     * @throws \Exception If username not found.
     *
     * @return int Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');

        $accountRepository = $this->entityManager->getRepository(Account::class);
        $account = $accountRepository->findOneBy(
            ['userId' => $username]
        );

        if ($account instanceof Account) {
            $rgList = [];
            $activeUser = false;
            $person = $account->getPerson();
            $researchGroups = $person->getResearchGroups();
            foreach ($researchGroups as $researchGroup) {
                if (!$researchGroup->isLocked()) {
                    $rgList[] = $researchGroup->getShortName();
                    $activeUser = true;
                }
            }
            if ($activeUser) {
                $io->note(sprintf('Username: %s: ACTIVE (active in: %s).', $username, implode(',', $rgList)));
            } else {
                $io->note(sprintf('Username: %s: INACTIVE (has account but no active RG).', $username));
            }
        } else {
            $io->note(sprintf('Username: %s: INACTIVE (has no account).', $username));
        }
        return Command::SUCCESS;
    }
}

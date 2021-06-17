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
 * Command to convert a user into a Pelagos DRPM.
 */
class CheckUserCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:CheckUser';

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
            setDescription('Check status of a user by username.')->
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
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');

        $accountRepository = $this->entityManager->getRepository(Account::class);
        $account = $accountRepository->findOneBy(
            ['userId' => $username]
        );

        if ($account instanceof Account) {
            $activeUser = false;
            $person = $account->getPerson();
            $researchGroups = $person->getResearchGroups();
            foreach ($researchGroups as $researchGroup) {
                if (false === $researchGroup->isLocked()) {
                    $rgList[] = $researchGroup->getShortName();
                    $activeUser = true;
                }
            }
            if (true === $activeUser) {
                $io->note(sprintf('Username: %s: ACTIVE (active in: %s).', $username, implode(',', $rgList)));
            } else {
                $io->note(sprintf('Username: %s: INACTIVE (has account but no active RG).', $username));
            }
        } else {
            $io->note(sprintf('Username: %s: INACTIVE (has no account).', $username));
        }
    }
}

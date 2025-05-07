<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Account;
use App\Entity\DataRepositoryRole;
use App\Entity\Person;
use App\Entity\PersonDataRepository;
use Symfony\Component\Process\Process;

/**
 * Command to convert a user into a Pelagos DRPM.
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:DRPMize', description: 'Promote an existing user into a DRPM.')]
class MakeDRPMCommand extends Command
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
            addArgument('username', InputArgument::OPTIONAL, 'User to make into DRPM.');
    }

    /**
     * Symfony command execute section.
     *
     * @param InputInterface  $input  Required by Command.
     * @param OutputInterface $output Required by Command.
     *
     * @throws \Exception If username not found.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');

        if (empty($username)) {
            $username = $_ENV['DRPM_USERNAME'] ?? trim((new Process(['whoami']))->mustRun()->getOutput());
        }

        $accountRepository = $this->entityManager->getRepository(Account::class);
        $account = $accountRepository->findOneBy(
            ['userId' => $username]
        );

        $dataRepositoryRoleRepository = $this->entityManager->getRepository(DataRepositoryRole::class);
        $DRPMDataRepositoryRole = $dataRepositoryRoleRepository->findOneBy(
            ['id' => 1]
        );

        if ($account instanceof Account) {
            $person = $account->getPerson();
            $personDataRepositoryRepository = $this->entityManager->getRepository(PersonDataRepository::class);
            $personDataRepository = $personDataRepositoryRepository->findOneBy(
                ['person' => $person]
            );

            $currentRole = $personDataRepository->getRole();
            if ($currentRole == $DRPMDataRepositoryRole) {
                $io->note(sprintf('Username %s is already a DRPM. No action taken.', $username));
            } else {
                $personDataRepository->setRole($DRPMDataRepositoryRole);
                $this->entityManager->persist($personDataRepository);
                $this->entityManager->flush();
            }
            $io->success($username . ' is now a DRPM.');
        } else {
            throw new \Exception('The username ' . $username . ' was not found in system.');
        }

        return Command::SUCCESS;
    }
}

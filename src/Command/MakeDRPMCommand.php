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

/**
 * Command to convert a user into a Pelagos DRPM.
 */
class MakeDRPMCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:DRPMize';

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
            setDescription('Promote an existing user into a DRPM.')->
            addArgument('username', InputArgument::REQUIRED, 'User to make into DRPM.');
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
    }
}

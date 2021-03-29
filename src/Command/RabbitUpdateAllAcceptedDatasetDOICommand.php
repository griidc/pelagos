<?php

namespace App\Command;

use App\Message\DoiMessage;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use App\Entity\Dataset;
use App\Entity\DIF;

use Symfony\Component\Messenger\MessageBusInterface;

/**
 * This command publishes a rabbit message for every accepted dataset forcing update of DOI info.
 *
 * @see Command
 */
class RabbitUpdateAllAcceptedDatasetDOICommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:dataset-doi:force-doi-update-all';

    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Symfony messenger bus interface.
     *
     * @var MessageBusInterface $messageBus
     */
    protected $messageBus;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param MessageBusInterface    $messageBus    Symfony messenger bus interface.
     */
    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $messageBus)
    {
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Force DOI update for all datasets having an accepted submission.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(array(
            'identifiedStatus' => DIF::STATUS_APPROVED));

        foreach ($datasets as $dataset) {
            $doiMessage = new DoiMessage($dataset->getId(), DoiMessage::ISSUE_OR_UPDATE);
            $this->messageBus->dispatch($doiMessage);
            echo 'Requesting DOI update for dataset ' . $dataset->getId() . ' (' . $dataset->getUdi() . ")\n";
        }
    }
}

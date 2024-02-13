<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Message\DoiMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * This command publishes a rabbit message for every accepted dataset forcing update of DOI info.
 *
 * @see Command
 */
#[AsCommand(
    name: 'pelagos:dataset-doi:force-doi-update-all',
    description: 'Force DOI update for all datasets having an accepted submission.',
)]
class UpdateAllAcceptedDatasetDOICommand extends Command
{
    /**
     * Class constructor for dependency injection.
     */
    public function __construct(private EntityManagerInterface $entityManager, private MessageBusInterface $messageBus)
    {
        parent::__construct();
    }

    /**
     * Executes the current command.
     *
     * @return int return code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $datasets = $this->entityManager->getRepository(Dataset::class)->getDatasetWithDoiSet();

        foreach ($datasets as $dataset) {
            $doiMessage = new DoiMessage((string) $dataset->getId(), DoiMessage::ISSUE_OR_UPDATE);
            $this->messageBus->dispatch($doiMessage);
            echo 'Requesting DOI update for dataset ' . $dataset->getId() . ' (' . $dataset->getUdi() . ")\n";
        }

        return 0;
    }
}

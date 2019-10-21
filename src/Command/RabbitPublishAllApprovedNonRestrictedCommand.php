<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Entity\DIF;
use App\Entity\Dataset;

/**
 * This command publishes a rabbit message for every accepted dataset forcing update of DOI info.
 *
 * @see Command
 */
class RabbitPublishAllApprovedNonRestrictedCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'dataset-doi:pub-all-appr-nonres-datasets';

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
     * A Rabbitmq producer instance.
     *
     * @var Producer $producer
     */
    protected $producer;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param Producer               $producer      A Rabbitmq producer instance.
     */
    public function __construct(EntityManagerInterface $entityManager, Producer $producer)
    {
        $this->entityManager = $entityManager;
        $this->producer = $producer;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Make DOI public for all approved, non-restricted datasets.');
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
            $this->producer->publish($dataset->getId(), 'update');
            $output->writeln('Attempting to publish/transition DOI for Dataset ' . $dataset->getId());
        }
    }
}

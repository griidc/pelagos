<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use App\Entity\DatasetSubmission;
use App\Entity\Dataset;
use App\Message\VirusScan;

/**
 * This command triggers a file virus scan, by UDI.
 */
class ScanFileByUDICommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:virusscan-file-by-udi';

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
      Symfony message bus interface.

      @var MessageBusInterface $bus
     */
    protected $bus;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param MessageBusInterface    $bus           A message bus interface.
     */
    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $bus)
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        parent::__construct();
    }

    /**
     * Symfony command config section.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Triggers a virus scan on a datasets file.')
            ->addArgument('UDI', InputArgument::REQUIRED, 'UDI to scan dataset file for.');
    }

    /**
     * Symfony command execution section.

     * @param InputInterface      $input  Command args.
     * @param OutputInterface     $output Output txt.
     * @param MessageBusInterface $bus    The message bus.
     *
     * @throws \Exception If cannot find dataset with provided UDI.
     * @throws \Exception If cannot find dataset submission in dataset.
     * @throws \Exception If cannot find file in dataset submission.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $udi = $input->getArgument('UDI');

        $datasets = $this->entityManager->getRepository(Dataset::class)->findBy(array('udi' => $udi));
        if (count($datasets) == 0) {
            throw new \Exception('Could not find a dataset with the udi provided.');
        }
        $dataset = $datasets[0];

        $datasetSubmission = $dataset->getDatasetSubmission();
        if (!($datasetSubmission instanceof DatasetSubmission)) {
            throw new \Exception('Could not find Dataset Submission.');
        }

        $fileset = $datasetSubmission->getFileset();
        $file = $fileset->getAllFiles()->first();
        $id = $file->getId();

        $this->bus->dispatch(new VirusScan($id));
        $output->writeln("Sent virus scan msg to bus with id: $id.");
    }
}

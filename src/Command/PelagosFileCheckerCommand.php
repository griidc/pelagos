<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Message\DatasetSubmissionFiler;
use App\Util\Datastore;
use App\Util\StreamInfo;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

class PelagosFileCheckerCommand extends Command
{
    protected static $defaultName = 'pelagos:file-checker';
    protected static $defaultDescription = 'Requeues Files';

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Pelagos Datastore.
     *
     * @var Datastore
     */
    private $datastore;

    /**
     * The message bus for dispatching the filer message.
     *
     * @var MessageBusInterface $messageBus
     */
    protected $messageBus;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param Datastore              $datastore     Datastore utility instance.
     * @param MessageBusInterface    $messageBus    The messenger bus.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Datastore $datastore,
        MessageBusInterface $messageBus
    ) {
        $this->entityManager = $entityManager;
        $this->datastore = $datastore;
        $this->messageBus = $messageBus;
        // It is required to call parent constructor if
        // using a constructon in a Symfony command.
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('udi', InputArgument::REQUIRED, 'The UDI')
            ->addOption('fix', null, InputOption::VALUE_NONE, 'Fix the file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fix = $input->getOption('fix');
        $udi = $input->getArgument('udi');

        /** @var Dataset $dataset */
        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(
            array('udi' => $udi)
        );

        $datasetSubmission = $dataset->getLatestDatasetReview();

        $files = $datasetSubmission->getFileset()->getAllFiles()->filter(function (File $file) {
            return $file->getStatus() === File::FILE_ERROR;
        });

        /** @var File $file */
        foreach ($files as $file) {
            $filePathName = $file->getFilePathName();
            if (str_contains($file->getPhysicalFilePath(), $filePathName)) {
                $file->setStatus(File::FILE_DONE);
            } else {
                // if filePathName contains "funkywhitespace"...
                if (preg_match("\p{C}+", $filePathName)) {
                    $file->setFilePathName(preg_replace("\p{C}+", ' ', $filePathName));
                }
                $file->setStatus(File::FILE_IN_QUEUE);
            }
        }

        $this->entityManager->flush();

        $datasetSubmissionFilerMessage = new DatasetSubmissionFiler($datasetSubmission->getId());
        $this->messageBus->dispatch($datasetSubmissionFilerMessage);

        $io->success('Done!');

        return 0;
    }
}

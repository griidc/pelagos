<?php

namespace App\Command;

use App\Entity\File;
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

class PelagosFileCheckerCommand extends Command
{
    protected static $defaultName = 'pelagos:file-checker';
    protected static $defaultDescription = 'Checks and fixes files';

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
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param Datastore              $datastore               Datastore utility instance.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Datastore $datastore
    ) {
        $this->entityManager = $entityManager;
        $this->datastore = $datastore;
        // It is required to call parent constructor if
        // using a constructon in a Symfony command.
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('fix', null, InputOption::VALUE_NONE, 'Fix the file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fix = $input->getOption('fix');

        $files = $this->entityManager->getRepository(File::class)->findBy(
            array('status' => 'error')
        );

        /** @var File $file */
        foreach ($files as $file) {
            $sha256Hash = $file->getFileSha256Hash();

            $filePath = $file->getPhysicalFilePath();
            $udi = $file->getFileset()->getDatasetSubmission()->getDataset()->getUdi();
            $errorMessage = '';
            try {
                $fileStream = $this->datastore->getFile($filePath);
                $fileHash = StreamInfo::calculateHash($fileStream);
            } catch (Exception $e) {
                $fileHash = 'ERROR';
                $errorMessage = $e->getMessage();
            }

            if ($sha256Hash === $fileHash) {
                $io->text("OK: udi:$udi, hash:$sha256Hash, file:$filePath");
                if ($fix) {
                    $file->setStatus('done');
                }
            } else {
                $io->text("BAD:$errorMessage udi:$udi, hash:$sha256Hash, file:$filePath");
            }

            $udi = $file->getFileset()->getDatasetSubmission()->getDataset()->getUdi();
        }

        $this->entityManager->flush();

        $io->success('Done!');

        return 0;
    }
}

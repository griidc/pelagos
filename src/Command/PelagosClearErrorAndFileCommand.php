<?php

namespace App\Command;

use App\Entity\File;
use App\Util\Datastore;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:clear-error-file', description: 'Removes an error-status file from fileset and if exists, filesystem.')]
class PelagosClearErrorAndFileCommand extends Command
{
    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Datastore util instance
     *
     * @var Datastore
     */
    protected $datastore;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     * @param Datastore              $datastore     The Pelagos datastore object.
     */
    public function __construct(EntityManagerInterface $entityManager, Datastore $datastore)
    {
        $this->entityManager = $entityManager;
        $this->datastore = $datastore;
        parent::__construct();
    }

    /**
     * Configure function to allow for options and parameters.
     */
    protected function configure(): void
    {
        $this
            ->addArgument('fileId', InputArgument::REQUIRED, 'ID of file in error to delete')
        ;
    }

    /**
     * Symfony command execute section.
     *
     * @param InputInterface  $input  Required by Command.
     * @param OutputInterface $output Required by Command.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fileId = $input->getArgument('fileId');

        $fileToDelete = $this->entityManager->getRepository(File::class)->findOneBy(
        // Only identify files in an error state.
            ['id' => $fileId, 'status' => File::FILE_ERROR]
        );

        if ($fileToDelete instanceof File) {
            try {
                $fileId = $fileToDelete->getId();
                $this->entityManager->remove($fileToDelete);
                $physicalFileToDelete = $fileToDelete->getPhysicalFilePath();
                if ($this->datastore->has($physicalFileToDelete)) {
                    $this->datastore->deleteFile($physicalFileToDelete);
                } else {
                    @unlink($physicalFileToDelete);
                }
                $this->entityManager->flush();
                $io->note("Removed file id: $fileId at: $physicalFileToDelete");
            } catch (\Exception $e) {
                $io->error("Could not delete file." . $e->getMessage());
            }
        } else {
            $io->warning("File specified ($fileId) is not in error or not found.");
        }
        return Command::SUCCESS;
    }
}

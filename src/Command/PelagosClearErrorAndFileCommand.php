<?php

namespace App\Command;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PelagosClearErrorAndFileCommand extends Command
{
    protected static $defaultName = 'pelagos:clear-error-file';
    protected static $defaultDescription = 'Removes an error-status file from fileset and if exists, filesystem.';

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
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;

        // It is required to call parent constructor if
        // using a constructon in a Symfony command.
        parent::__construct();
    }

    /**
     * Configure function to allow for options and parameters.
     */
    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
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
            array(
                'id' => $fileId,
                'status' => File::FILE_ERROR
            )
        );

        if ($fileToDelete instanceof File) {
            try {
                $delId = $fileToDelete->getId();
                $this->entityManager->remove($fileToDelete);
                $physicalFileToDelete = preg_match("/\/san\/home\/upload\//", $fileToDelete->getPhysicalFilePath()) ? $fileToDelete->getPhysicalFilePath() : '/san/data/store/' . $fileToDelete->getPhysicalFilePath();
                @unlink($physicalFileToDelete);
                $this->entityManager->flush();
                $io->note("Removed file id: $delId at: " . $fileToDelete->getPhysicalFilePath());
            } catch (\Exception $e) {
                $io->error("Could not delete file." . $e->getMessage());
            }
        } else {
            $io->warning("File specified ($fileId) is not in error or not found.");
        }
        return 0;
    }
}

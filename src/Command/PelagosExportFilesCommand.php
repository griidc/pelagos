<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\Fileset;
use App\Repository\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
#[AsCommand(
    name: 'pelagos:export-dataset-files',
    description: 'Generates an export of files for a dataset specified by UDI for review purposes.',
)]
class PelagosExportFilesCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private FileRepository $fileRepository;

    const string EXPORT_PATH = '/mnt/inspect-review-dataset';
    const string DATASTORE_PATH = '/san/data/store';

    public function __construct(EntityManagerInterface $entityManager, FileRepository $fileRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('UDI', InputArgument::REQUIRED, 'UDI of the dataset to export files for')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $udi = $input->getArgument('UDI');
        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(['udi' => $udi]);
        if ($dataset instanceof Dataset) {
            $fileset = $dataset->getDatasetSubmission()->getFileset();
            if ($fileset instanceof Fileset) {
                $this->exportFiles($fileset, $dataset->getUdi());
            } else {
                $io->error('No fileset found for the specified UDI.');
                return Command::FAILURE;
            }
        } else {
            $io->error('No dataset found for the specified UDI.');
            return Command::FAILURE;
        }

        $io->success('Export complete. Review in ' . self::EXPORT_PATH . '/' . str_replace(':', '.', $udi));

        return Command::SUCCESS;
    }

    protected function exportFiles($fileset, $udi)
    {
        $nudi = str_replace(':', '.', $udi);
        $fileIds = [];
        foreach ($fileset->getProcessedFiles() as $file) {
            $fileIds[] = $file->getId();
        }
        $filesInfo = $this->fileRepository->getFilePathNameAndPhysicalPath($fileIds);

        @mkdir(self::EXPORT_PATH . '/' . $nudi, 0755, true);
        $destinationPath = self::EXPORT_PATH . '/' . $nudi;

        foreach ($filesInfo as $fileItemInfo) {
            $sourceFileName = basename($fileItemInfo['physicalFilePath']);
            $sourcePath = SELF::DATASTORE_PATH . DIRECTORY_SEPARATOR . dirname($fileItemInfo['physicalFilePath']);
            $source = $sourcePath . DIRECTORY_SEPARATOR . $sourceFileName;
            $targetFileName = basename($fileItemInfo['filePathName']);
            $targetPath = $destinationPath . DIRECTORY_SEPARATOR . dirname($fileItemInfo['filePathName']);
            $target = $targetPath . DIRECTORY_SEPARATOR . $targetFileName;

            if (!is_dir($targetPath)) {
                @mkdir($targetPath , 0755, true);
            }
                copy($source, $target);
            }
        }
}

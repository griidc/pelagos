<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\Fileset;
use App\Repository\FileRepository;
use App\Util\Datastore;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Stream;
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
    private Datastore $datastore;

    public function __construct(EntityManagerInterface $entityManager, FileRepository $fileRepository, Datastore $datastore)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
        $this->datastore = $datastore;
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

        $io->success('Export complete. Review in /san/data/download/');

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

        @mkdir('/san_mwilliamson/data/download/exports-to-review/' . $nudi, 0755, true);
        $destinationPath = '/san_mwilliamson/data/download/exports-to-review/';

        foreach ($filesInfo as $fileItemInfo) {
            if (!is_dir(dirname($destinationPath . '/' . dirname($fileItemInfo['filePathName'])))) {
                @mkdir(dirname($destinationPath . '/' . dirname($fileItemInfo['filePathName'])) , 0755, true);
            }
            echo 'copying ' . $fileItemInfo['physicalFilePath'] . ' to: ' . $destinationPath . '/' . $nudi . '/' . $fileItemInfo['filePathName'] . "\n";
            stream_copy_to_stream(fopen('/san_mwilliamson/data/store/' . $fileItemInfo['physicalFilePath'], 'r'), fopen($destinationPath . '/' . $nudi . '/' . $fileItemInfo['filePathName'], 'w'));
        }
    }
}

<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\Fileset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'pelagos:get-file-formats',
    description: 'List formats and counts of each format of files in a non-remote dataset.',
)]

class PelagosGetFileFormatsCommand extends Command
{
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
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $datasets = $this->entityManager->getRepository(Dataset::class)->findAll();
        foreach ($datasets as $dataset) {
            $output = $dataset->getUdi() . ', ';
            /* Need dataset, with a submission, not remotely hosted, with a fileset, with at least one file. */
            if (!($dataset instanceof Dataset)) {
                $output .= 'No dataset found.';
                $io->writeln($output);
                continue;
            } else {
                $submission = $dataset->getDatasetSubmission();
            }

            if (!($submission instanceof DatasetSubmission)) {
                //No dataset submission
                continue;
            } else {
                $fileset = $submission->getFileset();
            }

            if ($dataset->isRemotelyHosted() === true) {
                $output .= 'Dataset is remotely hosted.';
                $io->writeln($output);
                continue;
            }

            if (!($fileset instanceof Fileset)) {
                $output .= 'No fileset found.';
                $io->writeln($output);
                continue;
            } else {
                $files = $fileset->getAllFiles();
            }

            if (count($files) < 1) {
                $output .= 'Fileset contains no files.';
                $io->writeln($output);
                continue;
            }

            $extensions = [];
            foreach ($files as $file) {
                $extension = $this->getExtension($file->getFilePathname());
                if (array_key_exists($extension, $extensions)) {
                    $extensions[$extension]++;
                } else {
                    $extensions[$extension] = 1;
                }
            }

            // sort by keys, case insensitive
            ksort($extensions, SORT_FLAG_CASE|SORT_NATURAL);

            $output = $dataset->getUdi() . ', ';
            foreach ($extensions as $key => $value) {
                $output .= $key . '(' . $value . ') ';
            }
            $output = rtrim($output, ' ');
            $io->writeln($output);
    }

        //$io->success('Filetype list complete.');
        return Command::SUCCESS;
    }

    /**
     * Gets filename extension, or 'no-extension'.
     *
     * @param  string $filename A file path and filename, with or without extensions.
     * @return string The extension, or the 'no-extensions' string if extensionless.
     */
    private function getExtension($filename) {
        $dotPos = strrpos($filename, '.');
        if ($dotPos === false) {
          return 'no-extension';
        }
        return substr($filename, $dotPos + 1);
      }
}

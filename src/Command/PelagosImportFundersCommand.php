<?php

namespace App\Command;

use App\Entity\Funder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'pelagos:import-funders',
    description: 'Import Funders from CSV',
)]
class PelagosImportFundersCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('csv', InputArgument::REQUIRED, 'CSV File Path to import from')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (false === $io->confirm('Are you sure you want to import all the Funders?', false)) {
            $io->caution('Opertation Aborted!');

            return Command::FAILURE;
        }

        $csvFilePath = $input->getArgument('csv');

        $funderData = [];

        $io->note("Getting Funders from $csvFilePath");
        $io->progressStart(count($funderData));

        if (($resource = fopen($csvFilePath, 'r')) !== false) {
            $keys = fgetcsv($resource, 0);
            while (($data = fgetcsv($resource)) !== false) {
                $funderData[] = array_combine($keys, $data);
                $io->progressAdvance();
            }
            fclose($resource);
            $io->progressFinish();

            $io->note('Importing Funders!');
            $io->progressStart(count($funderData));

            foreach ($funderData as $funderItem) {
                $io->progressAdvance();
                $funderName = $funderItem['primary_name_display'];
                $funderReferenceUri = $funderItem['uri'];
                $funderReplaced = 'R' == $funderItem['replaced'] ? true : false;

                if (true === $funderReplaced) {
                    continue;
                }

                $funderRepository = $this->entityManager->getRepository(Funder::class);

                $funder = $funderRepository->findOneBy(['referenceUri' => $funderReferenceUri]);

                if ($funder instanceof Funder) {
                    $funder->setName($funderName);
                } else {
                    $funder = new Funder();
                    $funder->setName($funderName);
                    $funder->setReferenceUri($funderReferenceUri);
                    $funder->setSource(Funder::SOURCE_IMPORTED);
                }

                $funderRepository->save($funder, false);
            }

            $io->progressFinish();
            $io->note('Flushing Funders!');
            $this->entityManager->flush();
        } else {
            $io->caution('No funders imported.');
        }

        $io->success('Done!');

        return Command::SUCCESS;
    }
}

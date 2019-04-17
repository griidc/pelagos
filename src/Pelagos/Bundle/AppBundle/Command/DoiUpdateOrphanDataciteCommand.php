<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Util\DOIutil;

/**
 * This Symfony Command updates the Datacite orphan dois.
 *
 * @see ContainerAwareCommand
 */
class DoiUpdateOrphanDataciteCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('doi:update-datacite-orphans')
            ->setDescription('Update Datacite orphans')
            ->addArgument('inputFileName', InputArgument::REQUIRED, 'List of orphan dois csv filename');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \RuntimeException Throws exception when no filename is provided.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputFileName = $input->getArgument('inputFileName');

        if ($inputFileName) {
            $contents = file($inputFileName);
        } else {
            throw new \RuntimeException('Please provide a filename or pipe template content to STDIN.');
        }

        foreach ($contents as $doi) {
            try {
                $doiUtil = new DOIutil();
                $doiUtil->updateDOI(
                    trim($doi),
                    'http://datacite.org/invalidDOI',
                    '(:null)',
                    'inactive',
                    'Harte Research Institute',
                    '2019',
                    'unavailable'
                );
            } catch (\Exception $e) {
                $output->writeln('Error for doi: ' . $doi . $e->getMessage());
            }
        }
    }
}

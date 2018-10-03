<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DIF;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Back fill Script for approved date attribute in DIF entity.
 *
 * @see ContainerAwareCommand
 */
class BackFillApprovedDateDifCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('dataset:backfill-dif-approved-date')
            ->setDescription('Backfill DIF approved date for approved datasets.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $datasets = $entityManager->getRepository(Dataset::class)->findBy(array('identifiedStatus' => DIF::STATUS_APPROVED));
        $count = 0;
        foreach ($datasets as $dataset) {
            $dif = $dataset->getDif();

            if ($dif->getStatus() === DIF::STATUS_APPROVED) {
                $dif->setApprovedDate($dif->getModificationTimeStamp());
                $output->writeln('Approved date back-filled for dataset: ' . $dataset->getId());
                $entityManager->persist($dataset);
                $count++;
            }
        }
        $entityManager->flush();
        $output->writeln('Total number of datasets which got back-filled: ' . $count);
    }
}

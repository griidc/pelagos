<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\DateTime;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DIF;

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

        $auditReader = $this->getContainer()->get('simplethings_entityaudit.reader');

        $datasets = $entityManager->getRepository(Dataset::class)->findBy(array('identifiedStatus' => DIF::STATUS_APPROVED));
        $count = 0;
        $approvedDateTimeStamp = new DateTime();
        foreach ($datasets as $dataset) {
            $dif = $dataset->getDif();

            if ($dif->getStatus() === DIF::STATUS_APPROVED) {
                $auditRevisionFinder = $auditReader->findRevisions(
                    'Pelagos\Entity\DIF',
                    $dif->getId()
                );

                for ($i = 0; $i < (count($auditRevisionFinder) - 1); $i++) {
                    // The revisions are ordered by latest first.
                    $newRevision = $auditRevisionFinder[$i];
                    $oldRevision = $auditRevisionFinder[($i + 1)];

                    $articleDiff = $auditReader->diff(
                        'Pelagos\Entity\DIF',
                        $dif->getId(),
                        $oldRevision->getRev(),
                        $newRevision->getRev()
                    );

                    if ($articleDiff['status']['new'] === DIF::STATUS_APPROVED) {
                        if (!empty($articleDiff['modificationTimeStamp']['new'])) {
                            $approvedDateTimeStamp = new \DateTime(
                                $articleDiff['modificationTimeStamp']['new']->format('Y-m-d H:i:s'),
                                new \DateTimeZone('+00:00')
                            );
                        } else {
                            $approvedDateTimeStamp = new \DateTime(
                                $articleDiff['modificationTimeStamp']['same']->format('Y-m-d H:i:s'),
                                new \DateTimeZone('+00:00')
                            );
                        }
                    }
                }

                if ($approvedDateTimeStamp instanceof DateTime) {
                    $dif->setApprovedDate($approvedDateTimeStamp);
                    $output->writeln('Approved date back-filled for dataset: ' . $dataset->getId());
                    $entityManager->persist($dataset);
                    $count++;
                } else {
                    $output->writeln('Modification Time stamp not an instance of DateTime for Dataset Id: ' . $dataset->getId());
                }
            }
        }
        $entityManager->flush();
        $output->writeln('Total number of datasets which got back-filled: ' . $count);
    }
}

<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        $datasets = $entityManager->getRepository(Dataset::class)->findBy(array('udi' => 'R4.x267.179:0028'));
        $count = 0;
        $approvedDateTimeStamp = new \DateTime();
        foreach ($datasets as $dataset) {
            $dif = $dataset->getDif();

            if ($dif->getStatus() === DIF::STATUS_APPROVED) {
                $auditRevisionFinder = $auditReader->findRevisions(
                    'Pelagos\Entity\DIF',
                    $dif->getId()
                );

                $numberOfRevisions = count($auditRevisionFinder);
                if ($numberOfRevisions > 1) {
                    for ($i = ($numberOfRevisions - 1); $i > 0; $i --) {
                        // The revisions are ordered by latest first.
                        $oldRevision = $auditRevisionFinder[$i];
                        $newRevision = $auditRevisionFinder[($i - 1)];

                        $articleDiff = $auditReader->diff(
                            'Pelagos\Entity\DIF',
                            $dif->getId(),
                            $oldRevision->getRev(),
                            $newRevision->getRev()
                        );

                        if ($articleDiff['status']['same'] === DIF::STATUS_APPROVED) {
                            $approvedDateTimeStamp = $articleDiff['modificationTimeStamp']['old'];
                            break;
                        } else if ($articleDiff['status']['new'] === DIF::STATUS_APPROVED) {
                            if (!empty($articleDiff['modificationTimeStamp']['new'])) {
                                $approvedDateTimeStamp = $articleDiff['modificationTimeStamp']['new'];
                                break;
                            } else {
                                $approvedDateTimeStamp = $articleDiff['modificationTimeStamp']['same'];
                                break;
                            }
                        }
                    }
                } else {
                        $approvedDateTimeStamp = $dif->getModificationTimeStamp();
                }

                if ($approvedDateTimeStamp instanceof \DateTime) {
                    //This is a workaround used to get a new DIF object from the entityManager with the same Id,
                    //because of the unknown behavior of auditReader on the previous DIF object.
                    $entityManager->clear();
                    $newDif = $entityManager->getRepository(DIF::class)->findOneBy(array('id' => $dif->getId()));

                    $newDif->setApprovedDate($approvedDateTimeStamp);
                    $output->writeln('Approved date back-filled for dataset: ' . $dataset->getId());
                    $entityManager->persist($newDif);
                    $entityManager->flush();
                    $count++;
                } else {
                    $output->writeln('Modification Time stamp not an instance of DateTime for Dataset Id: ' . $dataset->getId());
                }
                $approvedDateTimeStamp = null;
            }
        }
        $output->writeln('Total number of datasets which got back-filled: ' . $count);
    }
}

<?php

namespace Pelagos\Bundle\AppBundle\Command;


use Pelagos\Entity\ResearchGroup;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This Symfony Command populates the short names for Research groups.
 *
 * @see ContainerAwareCommand
 */
class PopulateResearchGroupShortNameCommand extends ContainerAwareCommand
{
    /**
     * Configuration for the command script.
     *
     * @return void
     */
    protected function configure() : void
    {

        $this
            ->setName('research-groups:populate-short-names')
            ->setDescription('Popualtes the short names for Research groups')
            ->addArgument('inputFileName', InputArgument::REQUIRED, 'Csv file containing short names?');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \Exception Exception thrown when openIO function fails to generate report.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputFileName = $input->getArgument('inputFileName');
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

        $fileContents = array_map('str_getcsv', file($inputFileName));
        array_walk($fileContents, function(&$combine) use ($fileContents) {
            $combine = array_combine($fileContents[0], $combine);
        });
        array_shift($fileContents);

        foreach ($fileContents as $fileContent) {
            $researchGroupName = $fileContent['Full Name'];
            $researchGroups = $entityManager->getRepository(ResearchGroup::class)->findBy(array(
                'name' => $researchGroupName
            ));

            if (count($researchGroups) > 0) {
                $researchGroup = $researchGroups[0];
                if ($researchGroup instanceof ResearchGroup) {
                    $researchGroup->setShortName($fileContent['Ro Short Name']);
                    $entityManager->persist($researchGroup);
                    $entityManager->flush();
                }
            } else {
                $output->writeln('Research group not found : ' . $researchGroupName);
            }
        }
    }
}
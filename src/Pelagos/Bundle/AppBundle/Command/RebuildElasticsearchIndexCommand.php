<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pelagos\Entity\Dataset;

/**
 * This class re-indexes the elasticsearch index.
 */
class RebuildElasticsearchIndexCommand extends ContainerAwareCommand
{

    /**
     * The Doctrine entity manager - ORM critter.
     *
     * @var EntityManager entityManager
     */
    protected $entityManager;

    /**
     * The elastica persister for the pelagos Dataset index.
     *
     * @var mixed pelagosDatasetIndexPersister
     */
    protected $pelagosDatasetIndexPersister;

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('elasticsearch:reindex-datasets')
            ->setDescription('Description: re-indexes datasets into elasticsearch')
            ->addArgument(
                'UDI',
                InputArgument::OPTIONAL,
                'Optional UDI to individually index - defaults to all if unspecified'
            );
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->pelagosDatasetIndexPersister = $this->getContainer()
            ->get('fos_elastica.object_persister.pelagos.dataset');

        $udi = $input->getArgument('UDI');
        if (null !== $udi) {
            $allDatasets = $this->entityManager->getRepository('Pelagos\Entity\Dataset')->findBy(array('udi' => $udi));
        } else {
            $allDatasets = $this->entityManager->getRepository('Pelagos\Entity\Dataset')->findAll();
        }
        $count = count($allDatasets);
        $counter = 0;
        $output->writeln($count . ' datasets found.');
        foreach ($allDatasets as $dataset) {
            $counter++;
            $output->writeln('indexing ' . $dataset->getUdi());
            $startTime = new \DateTime;
            $this->pelagosDatasetIndexPersister->insertOne($dataset);
            $elapsedTime = date_diff($startTime, new \DateTime);
            $elapsedTimeSeconds = $elapsedTime->format('%s');
            $output->writeln('     ' . $dataset->getUdi() . " indexed. ($counter/$count $elapsedTimeSeconds sec)");
        }
        return 0;
    }
}

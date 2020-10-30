<?php

namespace App\Command;

use App\Entity\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use FOS\ElasticaBundle\Persister\ObjectPersister;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class re-indexes the elasticsearch index.
 */
class RebuildElasticsearchIndexCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:elasticsearch-reindex-datasets';

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * The elastica persister for the pelagos Dataset index.
     *
     * @var ObjectPersister pelagosDatasetIndexPersister
     */
    protected $pelagosDatasetIndexPersister;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager                An instance of entity manager.
     * @param ObjectPersister        $pelagosDatasetIndexPersister FOS Elastica persister to populate the elasticsearch.
     */
    public function __construct(EntityManagerInterface $entityManager, ObjectPersister $pelagosDatasetIndexPersister)
    {
        $this->entityManager = $entityManager;
        $this->pelagosDatasetIndexPersister = $pelagosDatasetIndexPersister;
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
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
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $udi = $input->getArgument('UDI');
        if (null !== $udi) {
            $allDatasets = $this->entityManager->getRepository(Dataset::class)->findBy(array('udi' => $udi));
        } else {
            $allDatasets = $this->entityManager->getRepository(Dataset::class)->findAll();
        }
        $count = count($allDatasets);
        $counter = 0;
        $output->writeln($count . ' datasets found.');
        foreach ($allDatasets as $dataset) {
            $counter++;
            $output->writeln('indexing ' . $dataset->getUdi());
            $startTime = new \DateTime();
            $this->pelagosDatasetIndexPersister->insertOne($dataset);
            $elapsedTime = date_diff($startTime, new \DateTime());
            $elapsedTimeSeconds = $elapsedTime->format('%s');
            $output->writeln('     ' . $dataset->getUdi() . " indexed. ($counter/$count $elapsedTimeSeconds sec)");
        }
    }
}

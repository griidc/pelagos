<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Dataset;
use App\Entity\DatasetLink;
use App\Entity\DatasetSubmission;
use App\Entity\Person;

/**
 * This command sets the issue ticket on a datset (submisison).
 */
class SetErddapCommand extends Command
{
    /**
     * The Command name.
     *
     * @var string $defaultName
     */
    protected static $defaultName = 'pelagos:dataset-set-erddap';

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

    /**
     * Symfony command config section.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Sets an ERDDAP link for a dataset.')
            ->addOption('udi', null, InputOption::VALUE_REQUIRED, 'UDI of dataset to add erddap link to')
            ->addOption('url', null, InputOption::VALUE_REQUIRED, 'URL of ERDDAP link')
            ;
    }

    /**
     * Symfony command execution section.
     *
     * @param InputInterface  $input  Command args.
     * @param OutputInterface $output Output txt.
     *
     * @return integer Return code.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $systemPerson = $this->entityManager->find(Person::class, 0);
        $io = new SymfonyStyle($input, $output);

        $udi = $input->getOption('udi');
        $erddapUrl =$input->getOption('url');

        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(array('udi' => $udi));
        if (!($dataset instanceof Dataset)) {
            $io->error("Could not find a dataset with UDI ($udi)");
        } else {
            // get submission
            $submission = $dataset->getDatasetSubmission();
            if ($submission instanceof DatasetSubmission) {
                // create dataset link
                $oldLink = $submission->getErdappDatasetLink();
                if ($oldLink instanceof DatasetLink) {
                    $io->warning("$udi already has an erddap link. Not changing.");
                } else {
                    $link = new DatasetLink();
                    $link->setCreator($systemPerson);
                    $link->setModifier($systemPerson);

                    $link->setName(DatasetLink::LINK_NAME_CODES["erddap"]["name"]);
                    $link->setUrl($erddapUrl);
                    $linkDescription =
                    'ERDDAP infomation table listing individual dataset files links for this dataset. '
                    . 'Table is also available in other file formats (.csv, .htmlTable, .itx, .json, '
                    . '.jsonlCSV1, .jsonlCSV, .jsonlKVP, .mat, .nc, .nccsv, .tsv, .xhtml) via a RESTful '
                    . 'web service.';

                    $link->setDescription($linkDescription);
                    //$link->setFunctionCode(DatasetLink::ONLINE_FUNCTION_CODES["download"]["code"]);
                    $link->setFunctionCode('download');
                    $link->setProtocol('https');

                    $submission->addDatasetLink($link);
                    $this->entityManager->persist($dataset);
                    $this->entityManager->flush();
                    $io->success("Set ERDDAP URL on $udi");
                }
            } else {
                $io->error("$udi has no submission.");
            }
        }
        return 0;
    }
}

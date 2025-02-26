<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Dataset;
use App\Entity\DatasetLink;
use App\Entity\DatasetSubmission;
use App\Entity\Person;
use DateTime;
use DateTimeZone;

/**
 * This command adds a ERDDAP or NCEI dataset link.
 */
#[\Symfony\Component\Console\Attribute\AsCommand(name: 'pelagos:dataset-add-link', description: 'Sets an ERDDAP or a NCEI link for a dataset.')]
class AddDatasetLinkCommand extends Command
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

    /**
     * Symfony command config section.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->addOption('udi', null, InputOption::VALUE_REQUIRED, 'UDI of dataset to add erddap link to')
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'link type: ERDDAP or NCEI')
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
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $systemPerson = $this->entityManager->find(Person::class, 0);
        $io = new SymfonyStyle($input, $output);

        $udi = $input->getOption('udi');
        $type = $input->getOption('type');
        $targetUrl = $input->getOption('url');

        // Since Symfony doesn't allow mandatory options, and arguments are ordered and undescribed, not using.  Instead force options mandatory.
        if (empty($udi) or empty($type) or empty($targetUrl)) {
            $io->error("UDI, type, and URL parameters are not optional.");
            return Command::FAILURE;
        }

        // Accept only known types, 'NCEI' or 'ERDDAP'.
        if (!in_array($type, [DatasetLink::LINK_NAME_CODES["erddap"]["name"], DatasetLink::LINK_NAME_CODES["ncei"]["name"]])) {
            $io->error("Please specify either ERDDAP or NCEI.");
            return Command::FAILURE;
        }

        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(['udi' => $udi]);
        if (!($dataset instanceof Dataset)) {
            $io->error('Could not find a dataset with UDI (' . $udi . ')');
            return Command::FAILURE;
        } else {
            // get submission
            $submission = $dataset->getDatasetSubmission();
            if ($submission instanceof DatasetSubmission) {
                // create dataset link
                if (
                    ($type === DatasetLink::LINK_NAME_CODES["erddap"]["name"] and !empty($submission->getErddapUrl())) or
                    ($type === DatasetLink::LINK_NAME_CODES["ncei"]["name"] and !empty($submission->getNceiUrl()))
                ) {
                    $io->warning("$udi already has link of type $type. Not changing.");
                } else {
                    $link = new DatasetLink();

                    // common elements
                    $link->setProtocol('https');
                    $link->setCreator($systemPerson);
                    $link->setModifier($systemPerson);
                    $link->setCreationTimeStamp(new DateTime('now', new DateTimeZone('UTC')));
                    $link->setName($type);
                    $link->setUrl($targetUrl);

                    if ($type === DatasetLink::LINK_NAME_CODES["erddap"]["name"]) {
                        $linkDescription =
                        'ERDDAP infomation table listing individual dataset files links for this dataset. '
                        . 'Table is also available in other file formats (.csv, .htmlTable, .itx, .json, '
                        . '.jsonlCSV1, .jsonlCSV, .jsonlKVP, .mat, .nc, .nccsv, .tsv, .xhtml) via a RESTful '
                        . 'web service.';
                        $link->setFunctionCode('download');
                    } else {
                        $linkDescription = 'Navigate directly to the URL for a descriptive web page with download links.';
                        $link->setFunctionCode('information');
                    }
                    $link->setDescription($linkDescription);

                    $submission->addDatasetLink($link);
                    $this->entityManager->persist($submission);
                    $this->entityManager->flush();
                    $io->success("Set $type URL on $udi");
                }
            } else {
                $io->error("$udi has no submission.");
                return Command::FAILURE;
            }
        }
        $io->success('Command Complete.');
        return Command::SUCCESS;
    }
}

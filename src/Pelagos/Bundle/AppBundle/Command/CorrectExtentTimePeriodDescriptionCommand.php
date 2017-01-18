<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Metadata;
use Pelagos\Entity\Person;

/**
 * Correct the accepted metadata extent time period descriptions to new list.
 *
 * @see ContainerAwareCommand
 */
class CorrectExtentTimePeriodDescriptionCommand extends ContainerAwareCommand
{
    /**
     * A boolean to check if bad descriptions were found.
     *
     * @var boolean
     */
    private $foundBadRoles;

    /**
     * The number of time periods that were modified.
     *
     * @var integer
     */
    private $numberModified = 0;

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('metadata:correct-timeperiod')
            ->setDescription('Correct the accepted metadata extent time period descriptions to new list.')
            ->addArgument('PersonID', InputArgument::REQUIRED, 'What is the Person ID of the record modifier?');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \Exception When person is not found.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $personID = $input->getArgument('PersonID');

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $qb = $entityManager
            ->getRepository('Pelagos\Entity\Dataset')
            ->createQueryBuilder('dataset');

        $qb
            ->where('dataset.metadataStatus = :metadataStatus')
            ->andWhere('dataset.udi NOT LIKE :udi')
            ->setParameter('udi', 'BP%')
            ->setParameter('metadataStatus', DatasetSubmission::METADATA_STATUS_ACCEPTED);

        $datasets = $qb->getQuery()->getResult();

        foreach ($datasets as $dataset) {
            $this->foundBadRoles = false;
            $metadataRoleModified = false;
            $datasetRoleModified = false;

            $metadata = $dataset->getMetadata();

            if (!$metadata instanceof Metadata) {
                // Skip datasets without metadata.
                continue;
            }

            $xml = $metadata->getXml();

            if (!$xml instanceof \SimpleXMLElement) {
                // Skip datasets without valid xml metadata.
                continue;
            }

            $doc = new \DomDocument('1.0', 'UTF-8');
            $doc->loadXML($xml->asXml());

            $xpathdoc = new \DOMXpath($doc);

            $xpath = '/gmi:MI_Metadata' .
                '/gmd:identificationInfo' .
                '/gmd:MD_DataIdentification' .
                '/gmd:extent' .
                '/gmd:EX_Extent' .
                '/gmd:temporalElement' .
                '/gmd:EX_TemporalExtent' .
                '/gmd:extent' .
                '/gml:TimePeriod' .
                '/gml:description';

            $elements = $xpathdoc->query($xpath);

            if ($elements->length > 0) {
                $node = $elements->item(0);
                $nodeValue = (string) $node->nodeValue;

                $foundGroundCondition = preg_match('/ground.*condition/i', $nodeValue);
                $foundModeledPeriod = preg_match('/modeled.*period/i', $nodeValue);

                //Safety against overwrites.
                $newValue = $nodeValue;

                if ($foundGroundCondition and $foundModeledPeriod) {
                    $newValue = 'ground condition and modeled period';
                } elseif ($foundGroundCondition) {
                    $newValue = 'ground condition';
                } elseif ($foundModeledPeriod) {
                    $newValue = 'modeled period';
                }
                if ($nodeValue !== $newValue) {
                    $this->foundBadRoles = true;
                    $this->numberModified++;
                    $node->nodeValue = $newValue;
                }
            }

            if ($this->foundBadRoles) {
                $doc->formatOutput = true;
                $doc->normalizeDocument();

                $metadata->setXml(simplexml_load_string($doc->saveXML()));

                $modifier = $entityManager
                    ->getRepository('Pelagos\Entity\Person')
                    ->findOneBy(array('id' => $personID));

                if (!($modifier instanceof Person)) {
                    throw new \Exception("Could not find Modifier Person for PersonID ($personID) given.");
                }

                $metadata->setModifier($modifier);

                $entityManager->persist($metadata);
                $entityManager->persist($dataset);

                echo $dataset->getUdi() . ":\n";
                echo "  modified time period description to:$newValue\n";
            }
        }

        $entityManager->flush();

        $output->writeln('Modified ' . $this->numberModified . ' descriptions.');

        return 0;
    }
}

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
 * Change and correct Metadata Responsible Party Roles to new role set.
 *
 * @see ContainerAwareCommand
 */
class ChangeMetadataContactToNullCommand extends ContainerAwareCommand
{
    /**
     * The number of contacts that were modified.
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
            ->setName('metadata:change-nullcontact')
            ->setDescription('Change metadata contact e-mail to .null');
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
        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $datasets = $entityManager
            ->getRepository('Pelagos\Entity\Metadata')
            ->findAll();

        foreach ($datasets as $metadata) {
            $metadataModified = false;

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
                '/gmd:pointOfContact[1]' .
                '/gmd:CI_ResponsibleParty' .
                '/gmd:contactInfo' .
                '/gmd:CI_Contact' .
                '/gmd:address' .
                '/gmd:CI_Address' .
                '/gmd:electronicMailAddress' .
                '/gco:CharacterString';

            $elements = $xpathdoc->query($xpath);

            if ($elements->length > 0) {
                $node = $elements->item(0);
                $nodeValue = (string) $node->nodeValue;
                echo "E-mail:$nodeValue\n";
                if (!preg_match('/^.*\@.*\..*\.null/i', $nodeValue)) {
                    $node->nodeValue = (string) $node->nodeValue . '.null';
                    $this->numberModified++;
                    $metadataModified = true;
                } else {
                    echo "Skipping, already null\n";
                }
            }

            if ($metadataModified) {
                $doc->formatOutput = true;
                $doc->normalizeDocument();

                $metadata->setXml(simplexml_load_string($doc->saveXML()));

                $modifier = $entityManager
                    ->getRepository('Pelagos\Entity\Person')
                    ->findOneBy(array('id' => 0));

                $metadata->setModifier($modifier);

                $entityManager->persist($metadata);

                echo 'Modified: ' . $metadata->getId() . "\n";
            }
        }

        $entityManager->flush();


        $output->writeln('Modified ' . $this->numberModified . ' roles.');

        return 0;
    }
}

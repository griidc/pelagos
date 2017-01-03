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
class ChangeMetadataRolesCommand extends ContainerAwareCommand
{
    /**
     * A boolean to check if bad roles were found.
     *
     * @var boolean
     */
    private $foundBadRoles;

    /**
     * The number of roles that were modified.
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
            ->setName('metadata:change-roles')
            ->setDescription('Change the accepted metadata contact roles to new list.')
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
        $datasets = $entityManager
            ->getRepository('Pelagos\Entity\Dataset')
            ->findBy(array('metadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED));

        foreach ($datasets as $dataset) {
            $this->foundBadRoles = false;

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
                '/gmd:contact[1]' .
                '/gmd:CI_ResponsibleParty' .
                '/gmd:role' .
                '/gmd:CI_RoleCode';

            $elements = $xpathdoc->query($xpath);

            if ($elements->length > 0) {
                $node = $elements->item(0);
                $this->modifyRole($node);
            }

            $xpath = '/gmi:MI_Metadata' .
                '/gmd:identificationInfo' .
                '/gmd:MD_DataIdentification' .
                '/gmd:pointOfContact[1]' .
                '/gmd:CI_ResponsibleParty' .
                '/gmd:role' .
                '/gmd:CI_RoleCode';

            $elements = $xpathdoc->query($xpath);

            if ($elements->length > 0) {
                $node = $elements->item(0);
                $this->modifyRole($node);
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
            }
        }

        $entityManager->flush();

        $output->writeln('Modified ' . $this->numberModified . ' roles.');

        return 0;
    }

    /**
     * Modifies the given node, value and attributes according to role.
     *
     * @param \DOMNode $node A Dom Doc Node.
     *
     * @return void
     */
    private function modifyRole(\DOMNode &$node)
    {
        $nodeValue = (string) $node->nodeValue;
        if (!preg_match('/^pointOfContact$|^principalInvestigator$|^author$/', $nodeValue)) {

            $fixRole = array(
                'pointOfContact' =>
                array ('value' => 'pointOfContact', 'codeSpace' => '007'),
                'principalInvestigator' =>
                array ('value' => 'principalInvestigator', 'codeSpace' => '008'),
                'originator' =>
                array ('value' => 'principalInvestigator', 'codeSpace' => '008'),
                'custodian' =>
                array ('value' => 'pointOfContact', 'codeSpace' => '007'),
                'resourceProvider' =>
                array ('value' => 'pointOfContact', 'codeSpace' => '007'),
                'author' =>
                array ('value' => 'author', 'codeSpace' => '011'),
                'owner' =>
                array ('value' => 'principalInvestigator', 'codeSpace' => '008'),
            );

            foreach ($fixRole as $key => $role) {
                if (preg_match("/$key/i", $nodeValue)) {
                    $this->foundBadRoles = true;
                    $this->numberModified++;
                    $node->nodeValue = $role['value'];
                    $node->setAttribute('codeListValue', $role['value']);
                    $node->setAttribute('codeSpace', $role['codeSpace']);
                }
            }
        }
    }
}

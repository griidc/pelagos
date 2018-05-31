<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Report of UDIs that have html escape characters in metadata.
 *
 * @see ContainerAwareCommand
 */
class ReportMetadataHtmlEscapedCharactersCommand extends ContainerAwareCommand
{

    /**
     * Configuration for the command script.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('metadata:html-escaped-characters-report-command')
            ->setDescription('Report of udi(s) with html escaped chars in metadata file.');
    }

    /**
     * Script to generate the report.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @return integer Return 0 on success, or an error code otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // This command takes no input.
        unset($input);

        //array to store csv content
        $data = array();

        $count = 0;

        //regex for all html special characters
        $regex = '/(&.*;)/';

        $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');
        $metadataObjects = $entityManager
            ->getRepository(\Pelagos\Entity\Metadata::class)->findBy(array(), array('dataset' => 'DESC'));

        foreach ($metadataObjects as $metadata) {
            $xml = $metadata->getXml()->asXML();
            if (preg_match($regex, $xml)) {
                $udi = $metadata->getDataset()->getUdi();
                $count++;
                echo 'Dataset UDI ' . $udi . ' (' . $count . ")\n";
                $data[] = array($count, $udi);

            }
        }

        echo 'Total: ' . $count . "\n";

        //Create and write CSV file
        $fp = fopen('Report_Htmlescapedchars.csv', 'w');
        fputcsv($fp, array('No.', 'UDI'));
        foreach ($data as $line) {
            fputcsv($fp, $line);
        }
        fclose($fp);

        return 0;
    }
}

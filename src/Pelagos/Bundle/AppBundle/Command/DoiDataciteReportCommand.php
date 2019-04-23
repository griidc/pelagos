<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * This Symfony Command generates a Datacite DOI Report.
 *
 * @see ContainerAwareCommand
 */
class DoiDataciteReportCommand extends ContainerAwareCommand
{
    /**
     * A name of the file that will store the results of this report program.
     *
     * It is initialized to a default value.
     *
     * @var string
     */
    protected $outputFileName = '';

    /**
     * The Symfony Console output object.
     *
     * @var OutputInterface fileOutput
     */
    protected $fileOutput = null;

    /**
     * The file output array which stores the data.
     *
     * @var array
     */
    protected $fileOutputArray;

    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('reports:doi-datacite-report')
            ->setDescription('DOI report from Datacite.')
            ->addArgument('outputFileName', InputArgument::REQUIRED, 'What is the output file path and name?');
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
        $this->outputFileName = $input->getArgument('outputFileName');
        if ($this->fileOutput === null) {
            $handle = fopen($this->outputFileName, 'w');
            $this->fileOutput = new StreamOutput($handle);
        }
        $output->writeln('The output file is ' . $this->outputFileName);

        $this->createDataciteReport();
    }

    /**
     * Generate report for doi using Datacite info.
     *
     * @return void
     */
    private function createDataciteReport()
    {
        $headers = array(
            'doi',
            'target_url',
            'udi',
            'title',
            'created',
            'registered',
            'updated',
            'state',
            'resourceType'

        );

        $this->fileOutput->writeln(implode(',', $headers));

        $dois = $this->getDoiData();

        foreach ($dois as $doi) {
            $this->fileOutputArray = array();
            $this->fileOutputArray[] = $doi['doi'];
            $this->fileOutputArray[] = $doi['url'];
            $this->fileOutputArray[] = $doi['udi'];
            $this->fileOutputArray[] = $doi['title'];
            $this->fileOutputArray[] = $doi['created'];
            $this->fileOutputArray[] = $doi['registered'];
            $this->fileOutputArray[] = $doi['updated'];
            $this->fileOutputArray[] = $doi['state'];
            $this->fileOutputArray[] = $doi['resourceType'];

            $this->printResults();
        }
    }

    /**
     * Send the results to the printer.
     *
     * This also adds the comma delimiter.
     *
     * @return void
     */
    private function printResults(): void
    {
        if (count($this->fileOutputArray) >= 2) {
            $stringBuffer = '';
            for ($n = 0; $n < count($this->fileOutputArray); $n++) {
                if ($n >= 1) {
                    $stringBuffer .= ',';
                }
                $stringBuffer .= $this->fileOutputArray[$n];
            }
            $this->fileOutput->writeln($stringBuffer);
        }
    }

    /**
     * Get the doi data.
     *
     * @return array
     */
    private function getDoiData(): array
    {
        $client = new Client();
        $response = null;
        $doiJson = array();
        $pageNumber = 1;

        do {
            $url = 'https://api.datacite.org/dois?client-id=tdl.griidc&page%5Bnumber%5D=' . $pageNumber . '&page%5Bsize%5D=1000';
            $body = $this->getRestApiData($client, $url);
            $doiJson[$pageNumber] = $body['data'];
            $pageNumber++;
        } while (array_key_exists('next', $body['links']));

        $doiData = array();

        foreach ($doiJson as $dois) {
            foreach ($dois as $doi) {
                $doiData[$doi['id']] = array(
                    'doi' => $doi['attributes']['doi'],
                    'url' => $doi['attributes']['url'],
                    'udi' => $this->getUdi($doi['attributes']['url']),
                    'title' => str_replace(',', '', $doi['attributes']['titles'][0]['title']),
                    'created' => $doi['attributes']['created'],
                    'registered' => $doi['attributes']['registered'],
                    'updated' => $doi['attributes']['updated'],
                    'state' => $doi['attributes']['state'],
                    'resourceType' => $this->getResourceType($doi['attributes']['types'])
                );
            }
        }

        return $doiData;
    }

    /**
     * Get udi from Url.
     *
     * @param string $url Url that needs to be fetched.
     *
     * @return null
     */
    private function getUdi(string $url)
    {
        $udi = null;
        $udiRegEx = '/\b([A-Z\d]{2}\.x\d\d\d\.\d\d\d:\d\d\d\d)\b/';
        if (preg_match_all($udiRegEx, $url, $matches)) {
            trim(preg_replace($udiRegEx, '', $url));
            $udi = $matches[1][0];
        }

        return $udi;
    }

    /**
     * Get the resource type for the Doi.
     *
     * @param array $types Types of resources from doi.
     *
     * @return string
     */
    private function getResourceType(array $types): string
    {
        $resourceType = '';
        if (array_key_exists('resourceTypeGeneral', $types)) {
            $resourceType = $types['resourceTypeGeneral'];
        } elseif (array_key_exists('resourceType', $types)) {
            $resourceType = $types['resourceType'];
        }

        return $resourceType;
    }

    /**
     * Get a list of dois using Datacite REST API.
     *
     * @param Client $client Guzzle Http client instance.
     * @param string $url    Url that needs to be fetched.
     *
     * @return array
     */
    private function getRestApiData(Client $client, string $url): array
    {
        $header = ['Accept' => 'application/vnd.api+json'];

        try {
            $response = $client->request('get', $url, $header);
        } catch (GuzzleException $exception) {
            echo $exception->getMessage();
        }

        $body = json_decode($response->getBody()->getContents(), true);

        return $body;
    }
}

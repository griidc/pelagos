<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * This Symfony Command compares dois between griidc and datacite.
 *
 * @see ContainerAwareCommand
 */
class DoiComparisonCoomand extends ContainerAwareCommand
{
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
            ->setName('dataset-doi:comparison')
            ->setDescription('DOI comparison tool.');
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

        $this->syncConditions($doiData);
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

    private function syncConditions(array $doiData)
    {
        foreach ($doiData as $doi) {

        }

    }

    private function sendEmail()
    {
        if (!empty($errorUdi)) {
            $message = \Swift_Message::newInstance()
                ->setSubject('Error Log - List of Remotely Hosted Datasets links failed')
                ->setFrom(array('griidc@gomri.org' => 'GRIIDC'))
                ->setTo(array('griidc@gomri.org' => 'GRIIDC'))
                ->setCharset('UTF-8')
                ->setBody($this->getContainer()->get('templating')->render(
                    'PelagosAppBundle:Email:data-repository-managers.error-remotely-hosted.email.twig',
                    array('listOfUdi' => $errorUdi)
                ), 'text/html');
            $this->getContainer()->get('mailer')->send($message);
        }
    }
}

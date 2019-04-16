<?php

namespace Pelagos\Bundle\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * This Symfony Command updates the Datacite orphan dois.
 *
 * @see ContainerAwareCommand
 */
class DoiUpdateOrphanDataciteCommand extends ContainerAwareCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('doi:update-datacite-orphans')
            ->setDescription('Update Datacite orphans')
            ->addArgument('inputFileName', InputArgument::REQUIRED, 'List of orphan dois csv filename');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance.
     * @param OutputInterface $output An OutputInterface instance.
     *
     * @throws \RuntimeException Throws exception when no filename is provided.
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputFileName = $input->getArgument('inputFileName');

        if ($inputFileName) {
            $contents = file($inputFileName);
        } else {
            throw new \RuntimeException('Please provide a filename or pipe template content to STDIN.');
        }

        $iniFile = dirname(__FILE__) . '/../../../Util/DOIutil.ini';
        $parameters = parse_ini_file($iniFile);

        $url = $parameters['url_rest'] . '/dois';
        $doiUserName = $parameters['doi_api_user_name'];
        $doiPassword = $parameters['doi_api_password'];

        $defaultBody = [
            'data' => [
                'id' => null,
                'type' => 'dois',
                'attributes' => [
                    'creators' => [
                        ['name' => '(:null)']
                    ],
                    'titles' => [
                        ['title' => 'inactive']
                    ],
                    'publisher' => 'none supplied',
                    'url' => 'http://datacite.org/invalidDOI',
                    'event' => 'hide'
                ]
            ]
        ];
        $client = new Client();

        foreach ($contents as $doi) {
            $defaultBody['data']['id'] = trim($doi);
            try {
                $response = $client->request(
                    'PUT',
                    $url . '/' . trim($doi),
                    [
                        'auth' => [$doiUserName, $doiPassword],
                        'headers' => ['Content-Type' => 'application/json'],
                        'body' => json_encode($defaultBody)
                    ]
                );
            } catch (GuzzleException $e) {
                $output->writeln($e->getMessage());
            }
        }
    }
}

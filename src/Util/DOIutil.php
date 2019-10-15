<?php

namespace App\Util;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

use HylianShield\Encoding\Base32CrockfordEncoder;

use App\Entity\DOI;
use App\Exception\HttpClientErrorException;
use App\Exception\HttpServerErrorException;

/**
 * A utility to create and issue DOI from Datacite REST API.
 */
class DOIutil
{
    /**
     * The prefix for the GRIIDC doi.
     *
     * @var string
     */
    private $doiprefix;

    /**
     * The username for REST API.
     *
     * @var string
     */
    private $doiusername;

    /**
     * The password for REST API.
     *
     * @var string
     */
    private $doipassword;

    /**
     * The url for the API.
     *
     * @var string
     */
    private $url;

    /**
     * Constructor.
     *
     * Sets the REST API username, password, and prefix.
     *
     * @throws \Exception When ini file is not found.
     */
    public function __construct()
    {
        $iniFile = dirname(__FILE__) . '/DOIutil.ini';

        if (!file_exists($iniFile)) {
            throw new \Exception("$iniFile file not found!");
        }
        $parameters = parse_ini_file($iniFile);

        $this->doiprefix = $parameters['doi_api_prefix'];
        $this->doiusername = $parameters['doi_api_user_name'];
        $this->doipassword = $parameters['doi_api_password'];
        $this->url = $parameters['url'];
    }

    /**
     * Generates a random DOI in our namespace.
     *
     * @return string The generated DOI.
     */
    public function generateDoi() : string
    {
        $encoder = new Base32CrockfordEncoder();
        // 1099511627775 encodes to the longest 8 character Crockford 32 string.
        $max = 1099511627775;
        // Start at 1 (0 is problematic as library does not produce checksum for 0.)
        $random = random_int(1, $max);
        // Add prefix and remove the checksum character.
        return $this->doiprefix . '/' . substr($encoder->encode($random), 0, -1);
    }

    /**
     * This function will create a DOI.
     *
     * @param string $doi             The DOI identifier to create, or 'mint' to generate new.
     * @param string $url             URL for DOI.
     * @param string $creator         Creator for DOI.
     * @param string $title           Title for DOI.
     * @param string $publicationYear Published Date for DOI.
     * @param string $publisher       Publisher for DOI.
     * @param string $resourcetype    Type for DOI Request, by default Dataset.
     *
     * @throws HttpClientErrorException When there was an 4xx error negotiating with REST API.
     * @throws HttpServerErrorException When there was an 5xx error negotiating with REST API.
     *
     * @return void
     */
    public function createDOI(
        $doi,
        $url,
        $creator,
        $title,
        $publicationYear,
        $publisher,
        $resourcetype = 'Dataset'
    ) {
        $client = new Client();
        $defaultBody = [
            'data' => [
                'type' => 'dois',
                'attributes' => [
                    'doi' => $doi,
                    'creators' => [
                        ['name' => $creator]
                    ],
                    'titles' => [
                        ['title' => $title]
                    ],
                    'publisher' => $publisher,
                    'publicationYear' => $publicationYear,
                    'url' => $url,
                    'types' => [
                        'resourceTypeGeneral' => $resourcetype
                    ],
                ]
            ]
        ];

        try {
            $client->request(
                'POST',
                $this->url . '/dois',
                [
                    'auth' => [$this->doiusername, $this->doipassword],
                    'headers' => ['Content-Type' => 'application/vnd.api+json'],
                    'body' => json_encode($defaultBody, JSON_UNESCAPED_SLASHES)
                ]
            );
        } catch (ClientException $exception) {
            throw new HttpClientErrorException($exception->getMessage(), $exception->getCode());
        } catch (ServerException $exception) {
            throw new HttpServerErrorException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * This function will update a DOI.
     *
     * @param string $doi             The DOI to update.
     * @param string $url             URL for DOI.
     * @param string $creator         Creator for DOI.
     * @param string $title           Title for DOI.
     * @param string $publicationYear Published Date for DOI.
     * @param string $publisher       Publisher for DOI.
     * @param string $status          Status of the DOI.
     *
     * @throws HttpClientErrorException When there was an 4xx error negotiating with REST API.
     * @throws HttpServerErrorException When there was an 5xx error negotiating with REST API.
     *
     * @return void
     */
    public function updateDOI(
        $doi,
        $url,
        $creator,
        $title,
        $publicationYear,
        $publisher,
        $status = DOI::STATE_FINDABLE
    ) {
        $client = new Client();
        $defaultBody = [
            'data' => [
                'id' => $doi,
                'type' => 'dois',
                'attributes' => [
                    'creators' => [
                        ['name' => $creator]
                    ],
                    'titles' => [
                        ['title' => $title]
                    ],
                    'publisher' => $publisher,
                    'publicationYear' => $publicationYear,
                    'types' => [
                        'resourceTypeGeneral' => 'Dataset'
                    ],
                    'url' => $url,
                    'event' => ($status === DOI::STATE_FINDABLE) ? 'publish' : 'hide'
                ]
            ]
        ];

        try {
            $client->request(
                'PUT',
                $this->url . '/dois/' . $doi,
                [
                    'auth' => [$this->doiusername, $this->doipassword],
                    'headers' => ['Content-Type' => 'application/vnd.api+json'],
                    'body' => json_encode($defaultBody, JSON_UNESCAPED_SLASHES)
                ]
            );
        } catch (ClientException $exception) {
            throw new HttpClientErrorException($exception->getMessage(), $exception->getCode());
        } catch (ServerException $exception) {
            throw new HttpServerErrorException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * This function will get the DOI metadata for a DOI.
     *
     * @param string $doi DOI to get metadata for.
     *
     * @throws HttpClientErrorException Exception thrown when response code is 4xx negotiating with Datacite REST API.
     * @throws HttpServerErrorException Exception thrown when response code is 5xx negotiating with Datacite REST API.
     *
     * @return array
     */
    public function getDOIMetadata($doi)
    {
        $client = new Client();
        $metadata = array();
        $url = $this->url . '/dois/' . $doi;
        try {
            $response = $client->request(
                'GET',
                $url,
                ['auth' => [$this->doiusername, $this->doipassword],
                'headers' => ['Accept' => 'application/vnd.api+json']]
            );
        } catch (ClientException $exception) {
            throw new HttpClientErrorException($exception->getMessage(), $exception->getCode());
        } catch (ServerException $exception) {
            throw new HttpServerErrorException($exception->getMessage(), $exception->getCode());
        }

        $body = json_decode($response->getBody()->getContents(), true);

        if (array_key_exists('data', $body)) {
            $metadata = $body['data'];
        }

        return $metadata;
    }

    /**
     * This function will delete the unpublished DOI (i.e. in reserved state).
     *
     * @param string $doi DOI to delete.
     *
     * @throws HttpClientErrorException When there was an 4xx error negotiating with REST API.
     * @throws HttpServerErrorException When there was an 5xx error negotiating with REST API.
     *
     * @return void
     */
    public function deleteDOI($doi)
    {
        $doiMetadata = $this->getDOIMetadata($doi);
        if ($doiMetadata['attributes']['state'] === DOI::STATE_DRAFT) {
            $client = new Client();
            $url = $this->url . '/dois/' . $doi;
            try {
                $client->request(
                    'DELETE',
                    $url,
                    ['auth' => [$this->doiusername, $this->doipassword]]
                );
            } catch (ClientException $exception) {
                throw new HttpClientErrorException($exception->getMessage(), $exception->getCode());
            } catch (ServerException $exception) {
                throw new HttpServerErrorException($exception->getMessage(), $exception->getCode());
            }
        } else {
            $this->updateDOI(
                $doi,
                'http://datacite.org/invalidDOI',
                '(:null)',
                'inactive',
                '2019',
                'none supplied',
                DOI::STATE_REGISTERED
            );
        }
    }
}

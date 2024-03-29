<?php

namespace App\Util;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use App\Entity\DOI;
use App\Exception\HttpClientErrorException;
use App\Exception\HttpServerErrorException;
use App\Util\Base32Generator;

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
     * Datacite metadata attribute for relatedIdentifiers type of identifier.
     */
    const RELATED_IDENTIFIER_TYPE = 'DOI';

    /**
     * Datacite metadata attribute for relatedIdentifiers relation type of identifier.
     */
    const RELATION_TYPE = 'IsReferencedBy';

    /**
     * Constructor.
     *
     * Sets the REST API username, password, and prefix.
     *
     * @param string $doiApiUserName The API username.
     * @param string $doiApiPassword The API password.
     * @param string $doiApiPrefix   The API DOI prefix.
     * @param string $doiApiUrl      The API URL.
     */
    public function __construct(
        string $doiApiUserName,
        string $doiApiPassword,
        string $doiApiPrefix,
        string $doiApiUrl
    ) {
        $this->doiprefix = $doiApiPrefix;
        $this->doiusername = $doiApiUserName;
        $this->doipassword = $doiApiPassword;
        $this->url = $doiApiUrl;
    }

    /**
     * Generates a random DOI in our namespace.
     *
     * @return string The generated DOI.
     */
    public function generateDoi(): string
    {
        $idString = Base32Generator::generateId();
        return $this->doiprefix . '/' . $idString;
    }

    /**
     * This function will create a DOI.
     *
     * @param string      $doi             The DOI identifier to create, or 'mint' to generate new.
     * @param string      $url             URL for DOI.
     * @param string|null $creator         Creator for DOI.
     * @param string      $title           Title for DOI.
     * @param string|null $publicationYear Published Date for DOI.
     * @param string      $publisher       Publisher for DOI.
     * @param string      $resourcetype    Type for DOI Request, by default Dataset.
     *
     * @throws HttpClientErrorException When there was an 4xx error negotiating with REST API.
     * @throws HttpServerErrorException When there was an 5xx error negotiating with REST API.
     *
     * @return void
     */
    public function createDOI(
        string $doi,
        string $url,
        ?string $creator,
        string $title,
        ?string $publicationYear,
        string $publisher,
        string $resourcetype = 'Dataset'
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
     * @param array $publicationDois  Publication dois that are referenced.
     *
     * @throws HttpClientErrorException When there was an 4xx error negotiating with REST API.
     * @throws HttpServerErrorException When there was an 5xx error negotiating with REST API.
     *
     * @return void
     */
    public function updateDOI(
        string $doi,
        string $url,
        string $creator,
        string $title,
        string $publicationYear,
        string $publisher,
        array $publicationDois,
        string $status = DOI::STATE_FINDABLE
    ) {
        $client = new Client();
        $relatedIdentifiers = array();
        foreach ($publicationDois as $publicationDoi) {
            $relatedIdentifiers[] = array(
                'relatedIdentifier' => $publicationDoi,
                'relatedIdentifierType' => self::RELATED_IDENTIFIER_TYPE,
                'relationType' => self::RELATION_TYPE
            );
        }
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
                    'relatedIdentifiers' => $relatedIdentifiers,
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
    public function getDOIMetadata(string $doi)
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
    public function deleteDOI(string $doi)
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
                [],
                DOI::STATE_REGISTERED
            );
        }
    }

    /**
     * Get a collection of dois using Datacite REST API.
     *
     * @param integer $pageNo The required page number for the API.
     *
     * @return array
     */
    public function getDoiCollection(int $pageNo): array
    {
        $url = $this->url . '/dois' . '?client-id=' . strtolower($this->doiusername) . '&page%5Bnumber%5D=' . $pageNo . '&page%5Bsize%5D=1000';
        $header = ['Accept' => 'application/vnd.api+json'];
        $client = new Client();
        try {
            $response = $client->request('get', $url, $header);
        } catch (GuzzleException $exception) {
            echo $exception->getMessage();
        }

        $body = json_decode($response->getBody()->getContents(), true);

        return $body;
    }
}

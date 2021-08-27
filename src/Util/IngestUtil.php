<?php

namespace App\Util;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;

use App\Exception\HttpClientErrorException;
use App\Exception\HttpServerErrorException;

/**
 * A utility query the Ingest server API.
 */
class IngestUtil
{
    /**
     * The url for the API.
     *
     * @var string
     */
    private $url;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->url = 'http://griidc-ingest.tamucc.edu:3000/getFolders';
    }

    /**
     * This function will get a listing of ingest folders.
     *
     * @param string $user Username to get ingest/incoming/* folder listing for.
     *
     * @throws HttpClientErrorException Exception thrown when response code is 4xx negotiating with Ingest server REST API.
     * @throws HttpServerErrorException Exception thrown when response code is 5xx negotiating with Ingest server REST API.
     *
     * @return array
     */
    public function getUsersIngestFoldersInIncomingDir(string $username)
    {
        $client = new Client();
        $metadata = array();
        $url = $this->url . '/' . $username;
        try {
            $response = $client->request('GET', $url);
        } catch (ClientException $exception) {
            throw new HttpClientErrorException($exception->getMessage(), $exception->getCode());
        } catch (ServerException $exception) {
            throw new HttpServerErrorException($exception->getMessage(), $exception->getCode());
        }
        $data = json_decode($response->getBody()->getContents(), true);
        return $data;
    }
}

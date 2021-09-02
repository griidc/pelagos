<?php

namespace App\Util;

use GuzzleHttp\Client;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
    private $ingestApiUrl;

    /**
     * Constructor.
     *
     * @param string $ingestApiUrl The API URL.
     */
    public function __construct(
        string $ingestApiUrl
    ) {
        $this->ingestApiUrl = $ingestApiUrl;
    }

    /**
     * This function will get a listing of ingest folders.
     *
     * @param string $username Username to get ingest/incoming/* folder listing for.
     *
     * @throws HttpClientErrorException Exception thrown when response code is 4xx negotiating with Ingest server REST API.
     * @throws HttpServerErrorException Exception thrown when response code is 5xx negotiating with Ingest server REST API.
     *
     * @return array
     */
    public function getUsersIngestFoldersInIncomingDir(string $username): array
    {
        $client = new Client();
        $url = $this->ingestApiUrl . '/' . $username;
        try {
            $response = $client->request('GET', $url);
        } catch (\Exception $exception) {
            throw new HttpException($exception->getCode(), $exception->getMessage());
        } 
        return(json_decode($response->getBody()->getContents(), true));
    }
}

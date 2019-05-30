<?php

namespace Pelagos\Util;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

use GuzzleHttp\Exception\ServerException;
use HylianShield\Encoding\Base32CrockfordEncoder;

use Pelagos\Entity\DOI;
use Pelagos\Exception\HttpClientErrorException;
use Pelagos\Exception\HttpServerErrorException;

/**
 * A utility to create and issue DOI from EZID API.
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
     * The username for ezid.
     *
     * @var string
     */
    private $doiusername;

    /**
     * The password for ezid.
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
     * Sets the ezid username, password, and prefix.
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
     * @throws HttpClientErrorException When there was an 4xx error negotiating with EZID.
     * @throws HttpServerErrorException When there was an 5xx error negotiating with EZID.
     *
     * @return void
     */
    public function createDOI(
        $doi,
        $url,
        $creator,
        $title,
        $publicationYear,
        $publisher = 'Harte Research Institute',
        $resourcetype = 'Dataset'
    ) {
        $client = new Client();
        $defaultBody = [
            'data' => [
                'type' => 'dois',
                'attributes' => [
                    'doi' => $doi,
                    'creators' => [
                        ['name' => $this->escapeSpecialCharacters($creator)]
                    ],
                    'titles' => [
                        ['title' => $this->escapeSpecialCharacters($title)]
                    ],
                    'publisher' => $this->escapeSpecialCharacters($publisher),
                    'publicationYear' => $publicationYear,
                    'url' => $this->escapeSpecialCharacters($url),
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
                    'body' => json_encode($defaultBody)
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
     * @param string $publisher       Publisher for DOI.
     * @param string $publicationYear Published Date for DOI.
     * @param string $status          Status of the DOI.
     *
     * @throws HttpClientErrorException When there was an 4xx error negotiating with EZID.
     * @throws HttpServerErrorException When there was an 5xx error negotiating with EZID.
     *
     * @return void
     */
    public function updateDOI(
        $doi,
        $url,
        $creator,
        $title,
        $publisher,
        $publicationYear,
        $status = DOI::STATE_FINDABLE
    ) {
        $client = new Client();
        $defaultBody = [
            'data' => [
                'id' => $doi,
                'type' => 'dois',
                'attributes' => [
                    'creators' => [
                        ['name' => $this->escapeSpecialCharacters($creator)]
                    ],
                    'titles' => [
                        ['title' => $this->escapeSpecialCharacters($title)]
                    ],
                    'publisher' => $this->escapeSpecialCharacters($publisher),
                    'publicationYear' => $publicationYear,
                    'url' => $this->escapeSpecialCharacters($url),
                    'types' => [
                        'resourceTypeGeneral' => 'Dataset'
                    ],
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
                    'body' => json_encode($defaultBody)
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
     * @throws HttpClientErrorException When there was an 4xx error negotiating with EZID.
     * @throws HttpServerErrorException When there was an 5xx error negotiating with EZID.
     *
     * @return void
     */
    public function deleteDOI($doi)
    {
        $doiMetadata = $this->getDOIMetadata($doi);
        if ($doiMetadata['_status'] === 'reserved') {
            // Add doi: to doi is it doesn't exist.
            $doi = preg_replace('/^(?:doi:)?(10.\S+)/', 'doi:$1', $doi);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->url . '/id/' . $doi);
            curl_setopt($ch, CURLOPT_USERPWD, $this->doiusername . ':' . $this->doipassword);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            $output = curl_exec($ch);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            //check to see if it worked.
            if (200 != $httpCode) {
                $expMsg = "ezid failed with:$httpCode($output)";
                if ($httpCode >= 400 and $httpCode <= 499) {
                    throw new HttpClientErrorException($expMsg, $httpCode);
                } elseif ($httpCode >= 500 or $httpCode == 0) {
                    throw new HttpServerErrorException($expMsg, $httpCode);
                }
            }
        } else {
             $this->updateDOI(
                 $doi,
                 'http://datacite.org/invalidDOI',
                 '(:null)',
                 'inactive',
                 'none supplied',
                 '2019',
                 'unavailable'
             );
        }
    }

    /**
     * This function escape :%\n\r characters, because these are special with EZID.
     *
     * @param string $input Text that needs to be escaped.
     *
     * @return string The escaped string.
     */
    private function escapeSpecialCharacters($input)
    {
        return preg_replace_callback(
            '/[%:\r\n]/',
            function ($matches) {
                return sprintf('%%%02X', ord($matches[0]));
            },
            $input
        );
    }
}

<?php

namespace Pelagos\Util;

use Pelagos\Exception\HttpClientErrorException;
use Pelagos\Exception\HttpServerErrorException;
use HylianShield\Encoding\Base32CrockfordEncoder;

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
     * This function will create a DOI.
     *
     * @param string $doi             The DOI identifier to create, or 'mint' to generate new.
     * @param string $url             URL for DOI.
     * @param string $creator         Creator for DOI.
     * @param string $title           Title for DOI.
     * @param string $publisher       Publisher for DOI.
     * @param string $publicationYear Published Date for DOI.
     * @param string $status          Status of the DOI, by default is reserved.
     * @param string $resourcetype    Type for DOI Request, by default Dataset.
     *
     * @throws HttpClientErrorException When there was an 4xx error negotiating with EZID.
     * @throws HttpServerErrorException When there was an 5xx error negotiating with EZID.
     *
     * @return string The DOI issued by EZID.
     */
    public function createDOI(
        $doi,
        $url,
        $creator,
        $title,
        $publisher,
        $publicationYear,
        $status = 'reserved',
        $resourcetype = 'Dataset'
    ) {
        if ('mint' === $doi) {
            $encoder = new Base32CrockfordEncoder();
            // 1099511627775 encodes to the longest 8 character Crockford 32 string.
            $max = 1099511627775;
            // Start at 1 (0 is problematic as library does not produce checksum for 0.)
            $random = random_int(1, $max);
            // Add prefix and remove the checksum character.
            $doi = $this->doiprefix . '/' . substr($encoder->encode($random), 0, -1);
        }

        $input = '_target:' . $this->escapeSpecialCharacters($url) . "\n";
        $input .= "_profile:datacite\n";
        $input .= "_status:$status\n";
        $input .= 'datacite.creator:'
            . $this->escapeSpecialCharacters($creator)
            . "\n";
        $input .= 'datacite.title:'
            . $this->escapeSpecialCharacters($title)
            . "\n";
        $input .= 'datacite.publisher:' . $this->escapeSpecialCharacters($publisher) . "\n";
        $input .= "datacite.publicationyear:$publicationYear\n";
        $input .= "datacite.resourcetype:$resourcetype";

        utf8_encode($input);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . '/id/' . $doi);
        curl_setopt($ch, CURLOPT_USERPWD, $this->doiusername . ':' . $this->doipassword);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array('Content-Type: text/plain; charset=UTF-8','Content-Length: ' . strlen($input))
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //check to see if it worked.
        //using in array because EZID API returns 201 and EZDatacite API returns 200.
        if (!in_array($httpCode, [200, 201])) {
            $expMsg = "ezid failed with:$httpCode($output)";
            if ($httpCode >= 400 and $httpCode <= 499) {
                throw new HttpClientErrorException($expMsg, $httpCode);
            } elseif ($httpCode >= 500 or $httpCode == 0) {
                throw new HttpServerErrorException($expMsg, $httpCode);
            }
        }

        return true;
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
     * @return boolean True if updated successfully.
     */
    public function updateDOI(
        $doi,
        $url,
        $creator,
        $title,
        $publisher,
        $publicationYear,
        $status = 'public'
    ) {

        // Add doi: to doi is it doesn't exist.
        $doi = preg_replace('/^(?:doi:)?(10.\S+)/', 'doi:$1', $doi);

        $input = '_target:' . $this->escapeSpecialCharacters($url) . "\n";
        $input .= 'datacite.creator:' . $this->escapeSpecialCharacters($creator) . "\n";
        $input .= 'datacite.title:' . $this->escapeSpecialCharacters($title) . "\n";
        $input .= 'datacite.publisher:' . $this->escapeSpecialCharacters($publisher) . "\n";
        $input .= "datacite.publicationyear:$publicationYear\n";
        $input .= "datacite.resourcetype:Dataset\n";

        $input .= '_status: ' . $status . "\n";

        utf8_encode($input);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . '/id/' . $doi);
        curl_setopt($ch, CURLOPT_USERPWD, $this->doiusername . ':' . $this->doipassword);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array('Content-Type: text/plain; charset=UTF-8','Content-Length: ' . strlen($input))
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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

        return true;
    }

    /**
     * This function will get the DOI metadata for a DOI.
     *
     * @param string $doi DOI to get metadata for.
     *
     * @throws HttpClientErrorException When there was an 4xx error negotiating with EZID.
     * @throws HttpServerErrorException When there was an 5xx error negotiating with EZID.
     *
     * @return array Array or metadata variables.
     */
    public function getDOIMetadata($doi)
    {
        // Add doi: to doi is it doesn't exist.
        $doi = preg_replace('/^(?:doi:)?(10.\S+)/', 'doi:$1', $doi);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url . '/id/' . $doi);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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

        $metadata = array();
        foreach (explode("\n", $output) as $line) {
            $split = preg_split('/:/', $line, 2);
            if (count($split) > 1) {
                $metadata[$split[0]] = trim($split[1]);
            }
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

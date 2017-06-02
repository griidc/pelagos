<?php

namespace Pelagos\Util;

/**
 * A utility to create and issue DOI from EZID API.
 */
class DOIutil
{
    /**
     * The shoulder for the GRIIDC doi.
     *
     * @var string
     */
    private $doishoulder;

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
     * Constructor.
     *
     * Sets the ezid username, password, and shoulder.
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

        $this->doishoulder = $parameters['doi_api_shoulder'];
        $this->doiusername = $parameters['doi_api_user_name'];
        $this->doipassword = $parameters['doi_api_password'];
    }

    /**
     * This function will create a DOI.
     *
     * @param string $url             URL for DOI.
     * @param string $creator         Creator for DOI.
     * @param string $title           Title for DOI.
     * @param string $publisher       Publisher for DOI.
     * @param string $publicationYear Published Date for DOI.
     * @param string $status          Status of the DOI, by default is reserved.
     * @param string $resourcetype    Type for DOI Request, by default Dataset.
     *
     * @throws \Exception When there was an error negotiating with EZID.
     *
     * @return string The DOI issued by EZID.
     */
    public function createDOI(
        $url,
        $creator,
        $title,
        $publisher,
        $publicationYear,
        $status = 'reserved',
        $resourcetype = 'Dataset'
    ) {
        $input = '_target:' . $this->escapeSpecialCharacters($url) . "\n";
        $input .= "_profile:datacite\n";
        $input .= "_status:$status\n";
        $input .= 'datacite.creator:' . $this->escapeSpecialCharacters($creator) . "\n";
        $input .= 'datacite.title:' . $this->escapeSpecialCharacters($title) . "\n";
        $input .= 'datacite.publisher:' . $this->escapeSpecialCharacters($publisher) . "\n";
        $input .= "datacite.publicationyear:$publicationYear\n";
        $input .= "datacite.resourcetype:$resourcetype";

        utf8_encode($input);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://ezid.cdlib.org/shoulder/' . $this->doishoulder);
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
        if (201 != $httpCode) {
            throw new \Exception("ezid failed with:$httpCode($output)", $httpCode);
        }

        $doi = preg_match('/^success: (doi:\S+)/', $output, $matches);

        return $matches[1];
    }
    
    /**
     * This function will create a DOI.
     *
     * @param string $doi             The DOI to update.
     * @param string $url             URL for DOI.
     * @param string $creator         Creator for DOI.
     * @param string $title           Title for DOI.
     * @param string $publisher       Publisher for DOI.
     * @param string $publicationYear Published Date for DOI.
     *
     * @throws \Exception When there was an error negotiating with EZID.
     *
     * @return boolean True if updated successfully.
     */
    public function updateDOI(
        $doi,
        $url,
        $creator,
        $title,
        $publisher,
        $publicationYear
    ) {
        // Add doi: to doi is it doesn't exist.
        $doi = preg_replace('/^(?:doi:)?(10.\S+)/', 'doi:$1', $doi);
        
        $input = '_target:' . $this->escapeSpecialCharacters($url) . "\n";
        $input .= 'datacite.creator:' . $this->escapeSpecialCharacters($creator) . "\n";
        $input .= 'datacite.title:' . $this->escapeSpecialCharacters($title) . "\n";
        $input .= 'datacite.publisher:' . $this->escapeSpecialCharacters($publisher) . "\n";
        $input .= "datacite.publicationyear:$publicationYear\n";
        
        utf8_encode($input);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://ezid.cdlib.org/id/' . 'doi:10.fgviokjgokrg');
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
            throw new \Exception("ezid failed with:$httpCode($output)", $httpCode);
        }
        
        return true;
    }

    /**
     * This function will get the DOI metadata for a DOI.
     *
     * @param string $doi DOI to get metadata for.
     *
     * @throws \Exception When there was an error negotiating with EZID.
     *
     * @return array Array or metadata variables.
     */
    public function getDOIMetadata($doi)
    {
        // Add doi: to doi is it doesn't exist.
        $doi = preg_replace('/^(?:doi:)?(10.\S+)/', 'doi:$1', $doi);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://ezid.cdlib.org/id/$doi");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //check to see if it worked.
        if (200 != $httpCode) {
            throw new \Exception("ezid failed with:$httpCode($output)", $httpCode);
        }

        $metadata = array();
        foreach (explode("\n", $output) as $line) {
            $metadata[] = preg_split('/:/', $line, 2);
        }

        return $metadata;
    }

    /**
     * This function will publish the DOI.
     *
     * @param string $doi DOI to publish.
     *
     * @throws \Exception When there was an error negotiating with EZID.
     *
     * @return boolean True is published successfully.
     */
    public function publishDOI($doi)
    {
        // Add doi: to doi is it doesn't exist.
        $doi = preg_replace('/^(?:doi:)?(10.\S+)/', 'doi:$1', $doi);
        $input = '_status:public';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://ezid.cdlib.org/id/$doi");
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
            throw new \Exception("ezid failed with:$httpCode($output)", $httpCode);
        }

        return true;
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

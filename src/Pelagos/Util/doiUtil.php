<?php

namespace Pelagos\Util;

/**
 * A utility to create and issue DOI from EZID API.
 */
class doiUtil
{
    
    /**
     * This function will create a DOI.
     *
     * @param string $url   URL for DOI Request.
     * @param string $who   Creator for DOI Request.
     * @param string $what  Title for DOI Request.
     * @param string $where Publisher for DOI Request.
     * @param string $date  Published Date for DOI Request.
     * @param string $type  Type for DOI Request, by default Dataset.
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
    )
    {
        $input = '_target:' . $this->escapeSpecialCharacters($url) . "\n";
        $input .= "_profile:datacite\n";
        $input .= "_status:$status\n";
        $input .= 'datacite.creator:' . $this->escapeSpecialCharacters($creator) . "\n";
        $input .= 'datacite.title:' . $this->escapeSpecialCharacters($title) . "\n";
        $input .= 'datacite.publisher:' . $this->escapeSpecialCharacters($publisher) . "\n";
        $input .= "datacite.publicationyear:$publicationYear\n";
        $input .= "datacite.resourcetype:$resourcetype";
        
        $doishoulder = $this->getParameter('doi_api_shoulder');
        $doiusername = $this->getParameter('doi_api_user_name');
        $doipassword = $this->getParameter('doi_api_password');
        
        utf8_encode($input);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://ezid.cdlib.org/shoulder/$doishoulder");
        curl_setopt($ch, CURLOPT_USERPWD, "$doiusername:$doipassword");
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
            throw new \Exception("ezid failed with:$httpCode");
        }
        
        $doi = preg_match('/^success: (doi:\S+)/', $output, $matches);
        
        return $matches[1];
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
    public getDOIMetadata($doi)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ezid.cdlib.org/id/doi:$doi");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
        curl_close($ch);
        
        //check to see if it worked.
        if (200 != $httpCode) {
            throw new \Exception("ezid failed with:$httpCode");
        }
        
        $metadata = array();
        foreach(explode("\n", $output) as $line)
        {
            $metadata[] = preg_split("/:/", $line, 2);
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
    public publishDOI($doi)
    {
        $doishoulder = $this->getParameter('doi_api_shoulder');
        $doiusername = $this->getParameter('doi_api_user_name');
        
        $input = '_target: public';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ezid.cdlib.org/id/doi:$doi");
        curl_setopt($ch, CURLOPT_USERPWD, "$doiusername:$doipassword");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
        array('Content-Type: text/plain; charset=UTF-8',
            'Content-Length: ' . strlen($input)));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        
        //check to see if it worked.
        if (200 != $httpCode) {
            throw new \Exception("ezid failed with:$httpCode");
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
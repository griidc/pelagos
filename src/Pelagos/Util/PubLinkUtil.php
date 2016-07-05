<?php

namespace Pelagos\Util;

use Pelagos\Bundle\AppBundle\Handler\EntityHandler;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\Publication;
use Pelagos\Entity\PublicationCitation;

/**
 * This is a helper class for publication/dataset linking.
 */
class PubLinkUtil
{
    /**
     * Class constructor for Dependency Injection.
     *
     * @param EntityHandler $entityHandler The Pelagos EntityHandler class.
     */
    public function __construct(EntityHandler $entityHandler)
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * This method looks up a citation string at doi.org.
     *
     * @param string      $doi    The DOI of the publication.
     * @param string|null $style  The textual style desired.
     * @param string|null $locale The character representation desired.
     *
     * @return array
     */
    public function getCitationFromDoiDotOrg($doi, $style = 'apa', $locale = 'utf-8')
    {
        $statusCodes = array(
            200 => 'The request was OK.',
            204 => 'The request was OK but there was no metadata available.',
            404 => 'The DOI requested doesn\'t exist.',
            406 => 'Can\'t serve any requested content type.',
        );

        $ch = curl_init();
        $url = 'http://dx.doi.org/' . $doi;
        $header = array("Accept: text/bibliography; style=$style; locale=$locale");

        curl_setopt($ch, CURLOPT_URL, $url);
        // Since the request 303's (forwards) to http://data.crossref.org/, we have to turn follow on.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $curlResponse = curl_exec($ch);
        $errorText = curl_error($ch);
        $curlInfo = curl_getinfo($ch);
        curl_close($ch);
        $status = $curlInfo['http_code'];

        if ($status == 200) {
            $citation = new PublicationCitation($curlResponse, $style, $locale);
        } else {
            $citation = null;
        }

        return array('citation' => $citation, 'status' => $status, 'errorText' => $errorText);
    }

    /**
     * Generates a suggested citation for this dataset based on best available information.
     *
     * @return string;
     */
    public function generateSuggestedCitation()
    {
        $author = $this->getDatasetSubmission()->getAuthors();
        $year;
        $title;
        $doi;
        $udi;
        $regId;

        // determine $url to use.
        // The doi could include a url - check for it.
        if ($doi && strlen($doi) > 0) {
            // Does the doi contain the string 'http'?
            $pos = strpos($doi, 'http');
            // Make URL from DOI directly.
            if ($pos === false) {
                $url = 'http://dx.doi.org/' . $doi;
            // otherwise the stored DOI is a url.
            } else {
                $url = $doi;
            }
        } else {
            $url = 'http://data.gulfresearchinitiative.org/data/' . $udi;
        }

        $citationString = $author . ' (' . $year . '). ' . $title . ' (' . $udi . ') ' .
                   '[Data files] Available from ' . $url;
        return $citationString;
    }
}

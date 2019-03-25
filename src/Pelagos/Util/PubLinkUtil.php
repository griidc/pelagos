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
    public function fetchCitation($doi, $style = PublicationCitation::CITATION_STYLE_APA, $locale = 'en-US')
    {
        $curlCitation = $this->curlCitation($doi);
        $curlResponse = $curlCitation['curlResponse'];
        $status = $curlCitation['status'];
        $errorText = $curlCitation['errorText'];

        $citation = new PublicationCitation($curlResponse, $style, $locale);

        return array('citation' => $citation, 'status' => $status, 'errorText' => $errorText);
    }

    /**
     * This method looks up a citation string at doi.org.
     *
     * @param string       $doi         The DOI of the publication.
     * @param string|null  $style       The textual style desired.
     * @param string|null  $locale      The character representation desired.
     * @param boolean|null $useCrossRef Use the alternative way to retrieve the citation.
     *
     * @return array
     */
    private function curlCitation($doi, $style = PublicationCitation::CITATION_STYLE_APA, $locale = 'en-US', $useCrossRef = false)
    {
        $ch = curl_init();

        if (!$useCrossRef) {
            $url = 'http://dx.doi.org/' . $doi;
            $header = array("Accept: text/bibliography; style=$style; locale=$locale");
        } else {
            $url = "https://api.crossref.org/works/$doi/transform/text/x-bibliography";
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        // Since the request 303's forward, we have to turn follow on.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if (!empty($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $curlResponse = curl_exec($ch);
        $errorText = curl_error($ch);
        $curlInfo = curl_getinfo($ch);
        curl_close($ch);
        $status = $curlInfo['http_code'];
        $contentType = $curlInfo['content_type'];

        if ($status == 200 and !preg_match('/text\/bibliography/', $contentType) and !$useCrossRef) {
            return $this->curlCitation($doi, $style, $locale, true);
        }

        return array('curlResponse' => $curlResponse, 'status' => $status, 'errorText' => $errorText);
    }
}

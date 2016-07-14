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
        $statusCodes = array(
            200 => 'The request was OK.',
            204 => 'The request was OK but there was no metadata available.',
            404 => 'The DOI requested doesn\'t exist.',
            406 => 'Can\'t serve any requested content type.',
        );

        $ch = curl_init();
        $url = 'http://crosscite.org/citeproc/format' . '?doi=' . $doi . "&style=$style&locale=$locale";
        $header = array('Accept: text/plain;', "Accept-Language: $locale");

        curl_setopt($ch, CURLOPT_URL, $url);
        // Since the request 303's forward, we have to turn follow on.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
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
}

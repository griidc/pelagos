<?php

namespace Pelagos;

class Publication
{
    private $doi;
    private $citation;

    public function __construct($doi)
    {
        $this->doi = $doi;
        # TODO: retrieve citation from db, if exists
        # if citation in db: $this->citation = citation_from_db
    }

    public function getCitation()
    {
        return $this->citation;
    }

    public function pullCitation($style = 'apa', $locale = 'utf-8')
    {
        $statusCodes = array(
            200 => 'The request was OK.',
            204 => 'The request was OK but there was no metadata available.',
            404 => 'The DOI requested doesn\'t exist.',
            406 => 'Can\'t serve any requested content type.',
        );

        $ch = curl_init();
        $url = 'http://dx.doi.org/' . $this->doi;
        $header = array("Accept: text/bibliography; style=$style; locale=$locale");

        curl_setopt($ch, CURLOPT_URL, $url);
        // Since the request 303's (forwards) to http://data.crossref.org/, we have to turn follow on.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curlResponse = curl_exec($ch);
        $curlInfo = curl_getinfo($ch);
        curl_close($ch);

        $text = null;
        if ($curlInfo['http_code'] == 200) {
            $text = $curlResponse;
        }
        $status_message = null;
        if (array_key_exists($curlInfo['http_code'], $statusCodes)) {
            $status_message = $statusCodes[$curlInfo['http_code']];
        }
        $this->citation = new Citation($this->doi, $text, $style, $locale);
        return new HTTPStatus($curlInfo['http_code'], $status_message);
    }

    public function asJSON()
    {
        return json_encode(
            array(
                'doi' => $this->doi,
                'citation' => $this->citation,
            ),
            JSON_UNESCAPED_SLASHES
        );
    }
}

<?php

namespace Pelagos;

/**
 * Pelagos\Publication
 *
 * This class generates a Publication object,
 * which holds the DOI an a Citation object
 *
 *
 */
class Publication
{
    /**
     * DOI
     * @var $doi string Contains the DOI
     */
    private $doi;

    /**
     * Citation
     * @var $citation object Contains the Citation Object
     */
    private $citation;

    /**
     * Publication Constructor.
     *
     * @param string $doi DOI
     *
     * @return void
     */
    public function __construct($doi)
    {
        $this->doi = $doi;
    }

    /**
     * Retrieves a Publication Citation.
     *
     * This function retrieves a publication citation from DB cache
     * or will create an empty Citation
     *
     * @return Pelagos\Citation
     */
    public function getCitation()
    {
        require_once 'DBUtils.php';
        $connection = openDB('GOMRI_RO');

        $sth = $connection->prepare(
            'SELECT publication_citation, publication_citation_pulltime FROM publication WHERE publication_doi = :doi'
        );
        $sth->bindParam(':doi', $this->doi);
        $result = $sth->execute();
        if ($result and $sth->rowCount() > 0) {
            $citation = $sth->fetch(\PDO::FETCH_ASSOC);
            $this->citation = new Citation($this->doi, $citation['publication_citation']);
            $this->citation->setTimeStamp(new \DateTime($citation['publication_citation_pulltime']));
        }
        return $this->citation;
    }

    /**
     * Retrieved a Publication Citation.
     *
     * This function will get a Publication Citation from doi.org
     *
     * @param string $style (Optional) Style of the citation, default is 'apa'
     * @param string $locale (Optional) Locale of the citation text, default is 'utf-8'
     *
     * @return Pelagos\HTTPStatus
     */
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

        if ($curlInfo['http_code'] == 200) {
            $this->citation = new Citation($this->doi, $curlResponse, $style, $locale);
            require_once 'DBUtils.php';
            $connection = openDB('GOMRI_RW');
            $sth = $connection->prepare(
                 'update publication
                    set publication_citation = :citation, publication_citation_pulltime = :pull_date
                    where publication_doi = :doi;'
            );
            $sth->bindparam(':doi', $this->doi);
            $sth->bindparam(':citation', $curlResponse);
            $pull_date = date('c');
            $sth->bindparam(':pull_date', $pull_date);
            $result = $sth->execute();
            if (!$result) {
                return new HTTPStatus(500, $sth->errorInfo()[2]);
            }
            $sth = $connection->prepare(
                'insert into publication (publication_doi, publication_citation, publication_citation_pulltime)
                    select :doi, :citation, :pull_date
                    where not exists (select 1 from publication where publication_doi = :doi2);'
            );
            $sth->bindparam(':doi', $this->doi);
            $sth->bindparam(':doi2', $this->doi);
            $sth->bindparam(':citation', $curlResponse);
            $pull_date = date('c');
            $sth->bindparam(':pull_date', $pull_date);
            $result = $sth->execute();
            if (!$result) {
                return new HTTPStatus(500, $sth->errorInfo()[2]);
            }
        }
        $status_message = null;
        if (array_key_exists($curlInfo['http_code'], $statusCodes)) {
            $status_message = $statusCodes[$curlInfo['http_code']];
        }
        return new HTTPStatus($curlInfo['http_code'], $status_message);
    }

    /**
     * Return this class as JSON.
     *
     * @return JSON
     */

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

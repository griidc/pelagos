<?php

namespace App\Util;

use Elastica\Index;
use Elastica\ResultSet;
use Elastica\Query;

/**
 * A utility class for searching the Dataset index.
 */
class DatasetIndex
{
    /**
     * The Dataset elastic index.
     *
     * @var Index
     */
    protected $datasetIndex;

    /**
     * Constructor.
     *
     * @param Type $datasetIndex The Datatset elastic index.
     */
    public function __construct(Index $datasetIndex)
    {
        $this->datasetIndex = $datasetIndex;
    }

    /**
     * Search the Dataset index and return reusults.
     *
     * @param array  $termsFilters Filters that match any one of several values exactly.
     * @param string $text         Textual searh string.
     * @param string $geoFilter    WKT string representing polygon to filter with.
     *
     * @return ResultSet
     */
    public function search(array $termsFilters = array(), string $text = null, string $geoFilter = null)
    {
        $query = $this->buildQuery($termsFilters, $text, $geoFilter);
        if (empty(trim($text))) {
            $query->addSort(array('updatedDateTime' => array('order' => 'desc')));
        }
        return $this->datasetIndex->search($query);
    }

    /**
     * Search the Dataset index and count the reusults.
     *
     * @param array  $termsFilters Filters that match any one of several values exactly.
     * @param string $text         Textual searh string.
     * @param string $geoFilter    WKT string representing polygon to filter with.
     *
     * @return integer
     */
    public function count(array $termsFilters = array(), string $text = null, string $geoFilter = null)
    {
        $query = $this->buildQuery($termsFilters, $text, $geoFilter);
        return $this->datasetIndex->count($query);
    }

    /**
     * Build a query for the Dataset index.
     *
     * @param array  $termsFilters Filters that match any one of several values exactly.
     * @param string $text         Textual searh string.
     * @param string $geoFilter    WKT string representing polygon to filter with.
     *
     * @return Query
     */
    protected function buildQuery(array $termsFilters = array(), string $text = null, string $geoFilter = null)
    {
        $mainQuery = new Query\BoolQuery();

        $text = trim($text);

        if (!empty($text)) {
            $textQuery = new Query\BoolQuery();
            $udiRegEx = '/\b([A-Z\d]{2}\.x\d\d\d\.\d\d\d[:.]\d\d\d\d)\b/i';
            if (preg_match_all($udiRegEx, $text, $matches)) {
                $text = trim(preg_replace($udiRegEx, '', $text));
                foreach ($matches[1] as $udi) {
                    // Replacing the 11th position to ":"
                    $udi = substr_replace($udi, ':', 11, 1);
                    $textQuery->addShould(
                        new Query\MatchPhrase('udi', $udi)
                    );
                }
            }
            $doiRegEx = '!\b(?:[Dd][Oo][Ii]\s*:\s*)?(10.\d{4,9}/[-._;()/:A-Z0-9a-z]+)\b!';
            if (preg_match_all($doiRegEx, $text, $matches)) {
                $text = trim(preg_replace($doiRegEx, '', $text));
                foreach ($matches[1] as $doi) {
                    // Query against dataset DOIs.
                    $doiQuery = new Query\Nested();
                    $doiQuery->setPath('doi');
                    $doiQuery->setQuery(
                        new Query\MatchPhrase('doi.doi', $doi)
                    );

                    // Query against DOIs associated with the dataset.
                    $pubDoiQuery = new Query\Nested();
                    $pubDoiQuery->setPath('publications');
                    $pubDoiQuery->setQuery(
                        new Query\MatchPhrase('publications.doi', $doi)
                    );

                    $textQuery->addShould($doiQuery);
                    $textQuery->addShould($pubDoiQuery);

                    // Also check the titles and abstracts for mention of the given DOI.
                    $textQuery->addShould(new Query\MatchPhrase('title', $doi));
                    $textQuery->addShould(new Query\MatchPhrase('abstract', $doi));
                }
            }

            if (!empty($text)) {
                $datasetQuery = new Query\MultiMatch();
                $datasetQuery->setQuery($text);
                $datasetQuery->setFields(
                    array(
                        'title',
                        'abstract',
                    )
                );
                $textQuery->addShould($datasetQuery);

                $researchGroupNameQuery = new Query\Nested();
                $researchGroupNameQuery->setPath('researchGroup');
                $researchGroupNameQuery->setQuery(
                    new Query\MatchQuery('researchGroup.name', $text)
                );
                $textQuery->addShould($researchGroupNameQuery);

                $fundingOrgQuery = new Query\Nested();
                $fundingOrgQuery->setPath('researchGroup.fundingCycle.fundingOrganization');

                $fundingOrgQuery->setQuery(
                    new Query\MatchQuery('researchGroup.fundingCycle.fundingOrganization.name', $text)
                );
                $fundingCycleQuery = new Query\Nested();
                $fundingCycleQuery->setPath('researchGroup.fundingCycle');
                $fundingCycleQuery->setQuery($fundingOrgQuery);
                $researchGroupQuery = new Query\Nested();
                $researchGroupQuery->setPath('researchGroup');
                $researchGroupQuery->setQuery($fundingCycleQuery);
                $textQuery->addShould($researchGroupQuery);

                $datasetSubmissionQuery = new Query\Nested();
                $datasetSubmissionQuery->setPath('datasetSubmission');
                $datasetSubmissionQuery->setQuery(
                    new Query\MatchQuery('datasetSubmission.authors', $text)
                );
                $textQuery->addShould($datasetSubmissionQuery);

                // Query against DatasetSubmission placeKeywords associated with the dataset.
                $placeKeywordsQuery = new Query\Nested();
                $placeKeywordsQuery->setPath('datasetSubmission');
                $placeKeywordsQuery->setQuery(
                    new Query\MatchQuery('datasetSubmission.placeKeywords', $text)
                );
                $textQuery->addShould($placeKeywordsQuery);

                // Query against DatasetSubmission themeKeywords associated with the dataset.
                $themeKeywordsQuery = new Query\Nested();
                $themeKeywordsQuery->setPath('datasetSubmission');
                $themeKeywordsQuery->setQuery(
                    new Query\MatchQuery('datasetSubmission.themeKeywords', $text)
                );
                $textQuery->addShould($themeKeywordsQuery);
            }
            $mainQuery->addMust($textQuery);
        }

        if (null !== $geoFilter) {
            $geometry = \geoPHP::load($geoFilter, 'wkt');
            $mainQuery->addFilter(
                new Query\GeoShapeProvided(
                    'simpleGeometry',
                    $geometry->asArray(),
                    Query\GeoShapeProvided::TYPE_POLYGON
                )
            );
        }

        foreach ($termsFilters as $field => $terms) {
            $mainQuery->addFilter(
                $this->processTermsFilter($field, $terms)
            );
        }

        $query = new Query();
        $query->setSize(10000);
        $query->setQuery($mainQuery);

        //control of what will be returned
        $query->setSource(
            array(
                'id', 'udi', 'title', 'year', 'researchGroup', 'doi', 'datasetSubmission.authors',
                'datasetSubmission.datasetFileTransferStatus', 'datasetSubmission.datasetFileSize',
                'datasetSubmission.restrictions', 'geometry', 'researchGroup.fundingCycle.fundingOrganization.name'
            )
        );

        return $query;
    }

    /**
     * Process one terms filter that can be for a field in nested object.
     *
     * @param string  $field   The full path to the field to search.
     * @param array   $terms   An array of terms to search for.
     * @param integer $pointer A pointer to where we are in the path.
     *
     * @return Query\Terms|Query\Nested
     */
    protected function processTermsFilter(string $field, array $terms, int $pointer = 1)
    {
        $path = preg_split('/\./', $field);
        if ($pointer === count($path)) {
            return new Query\Terms($field, $terms);
        }
        $nestedQuery = new Query\Nested();
        $nestedQuery->setPath(implode('.', array_slice($path, 0, $pointer)));
        $nestedQuery->setQuery(
            $this->processTermsFilter($field, $terms, ($pointer + 1))
        );
        return $nestedQuery;
    }
}

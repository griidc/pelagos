<?php

namespace Pelagos\Util;

use Elastica\Query;
use Elastica\Type;

/**
 * A utility class for searching the Dataset index.
 */
class DatasetIndex
{
    /**
     * The Dataset elastic type.
     *
     * @var Type
     */
    protected $datasetType;

    /**
     * Constructor.
     *
     * @param Type $datasetType The Datatset elastic type.
     */
    public function __construct(Type $datasetType)
    {
        $this->datasetType = $datasetType;
    }

    /**
     * Search the Dataset index and return reusults.
     *
     * @param array  $termsFilters Filters that match any one of several values exactly.
     * @param string $text         Textual searh string.
     * @param string $geoFilter    WKT string representing polygon to filter with.
     *
     * @return array
     */
    public function search(array $termsFilters = array(), $text = null, $geoFilter = null)
    {
        $query = $this->buildQuery($termsFilters, $text, $geoFilter);
        return $this->datasetType->search($query);
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
    public function count(array $termsFilters = array(), $text = null, $geoFilter = null)
    {
        $query = $this->buildQuery($termsFilters, $text, $geoFilter);
        return $this->datasetType->count($query);
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
    protected function buildQuery(array $termsFilters = array(), $text = null, $geoFilter = null)
    {
        $mainQuery = new Query\BoolQuery();

        if (!empty($text)) {
            $textQuery = new Query\BoolQuery();
            $datasetQuery = new Query\MultiMatch();
            $datasetQuery->setQuery($text);
            $datasetQuery->setFields(
                array(
                    'udi',
                    'title',
                    'abstract',
                )
            );
            $textQuery->addShould($datasetQuery);
            $researchGroupQuery = new Query\Nested();
            $researchGroupQuery->setPath('researchGroup');
            $researchGroupQuery->setQuery(
                new Query\Match('researchGroup.name', $text)
            );
            $textQuery->addShould($researchGroupQuery);
            $datasetSubmissionQuery = new Query\Nested();
            $datasetSubmissionQuery->setPath('datasetSubmission');
            $datasetSubmissionQuery->setQuery(
                new Query\Match('datasetSubmission.authors', $text)
            );
            $textQuery->addShould($datasetSubmissionQuery);
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
    protected function processTermsFilter($field, array $terms, $pointer = 1)
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

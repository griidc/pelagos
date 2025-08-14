<?php

namespace App\Controller\UI;

use App\Enum\DatasetLifecycleStatus;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\GeoShapeProvided;
use Elastica\Query\MatchPhrase;
use Elastica\Query\MatchPhrasePrefix;
use Elastica\Query\Nested;
use Elastica\Query\Range;
use Elastica\Query\Terms;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class MapSearchController extends AbstractController
{
    public function __construct(
        private TransformedFinder $searchPelagosFinder,
    ) {
    }

    #[Route('/map-search', name: 'app_map_search')]
    public function index(): Response
    {
        return $this->render('MapSearch/index.html.twig');
    }

    /**
     * Parses the filter json string and returns the value for the specified field.
     *
     * @param string $filter   the filter string in JSON format
     * @param string $field    the field to extract the value for
     * @param bool   $matchAll whether to match all occurrences of the field in the filter
     *
     * @return mixed the value extracted from the filter for the specified field, or null if not found, will return an array if $matchAll is true
     */
    private function getValueFromFilterRegex(string $filter, string $field, bool $matchAll = false): mixed
    {
        /* Regex example patterns:
         *  /"(geometry)","([^"]+)",(\{.*\})/
         *  /"(title)","([^"]+)","([^"]+)"/
         */
        if ($matchAll) {
            preg_match_all('/\["(' . $field . ')","([^"]+)",((\{.*\})|"([^"]+)"|\[.*?[^]]?(?=\])\])/', $filter, $matches);
        } else {
            preg_match('/\["(' . $field . ')","([^"]+)",((\{.*\})|"([^"]+)"|\[.*?[^]]?(?=\])\])/', $filter, $matches);
        }

        if (count($matches) > 0) {
            return $matches[5] ?? $matches[3] ?? null;
        }

        return null;
    }

    #[Route('/map/search', name: 'app_map_search_search')]
    public function search(
        #[MapQueryParameter] ?int $take = 9999,
        #[MapQueryParameter] ?int $skip = 0,
        #[MapQueryParameter] ?array $filter = [],
        #[MapQueryParameter] ?array $group = [],
        #[MapQueryParameter] ?array $sort = [],
        #[MapQueryParameter] ?bool $requireTotalCount = false,
        #[MapQueryParameter] ?string $userData = '{}',
    ): Response {
        $query = new Query();

        if (is_array($sort) && !empty($sort)) {
            // TODO: replace next function with json_decode
            $sort = $this->getParseParams($sort, true);
            foreach ($sort as $item) {
                $query->addSort([$item[0]['selector'] => ['order' => $item[0]['desc'] ? 'DESC' : 'ASC']]);
            }
        }

        if (null !== $filter && [] !== $filter) {
            $mainQuery = new BoolQuery();

            $searchFilter = new BoolQuery();

            $field = 'udi';
            $value = $this->getValueFromFilterRegex($filter[0], $field);
            if (null !== $value) {
                $matchQuery = new MatchPhrase($field, $value);
                $searchFilter->addShould($matchQuery);
            }

            $field = 'title';
            $value = $this->getValueFromFilterRegex($filter[0], $field);
            if (null !== $value) {
                $matchQuery = new MatchPhrasePrefix($field, $value);
                $searchFilter->addShould($matchQuery);
            }

            $field = 'doi.doi';
            $value = $this->getValueFromFilterRegex($filter[0], $field);
            if (null !== $value) {
                $nestedQuery = new Nested();
                $nestedQuery->setPath('doi');
                $matchQuery = new MatchPhrase($field, $value);
                $nestedQuery->setQuery($matchQuery);
                $searchFilter->addShould($nestedQuery);
            }

            if ($searchFilter->count() > 0) {
                $mainQuery->addMust($searchFilter);
            }

            $filterQuery = new BoolQuery();

            $field = 'datasetLifecycleStatus';
            $value = $this->getValueFromFilterRegex($filter[0], 'datasetLifecycleStatus', true);
            if (!empty($value)) {
                $termQuery = new Terms($field);
                $termQuery->setTerms($value);
                $filterQuery->addFilter($termQuery);
            }

            $field = 'collectionStartDate';
            $value = $this->getValueFromFilterRegex($filter[0], 'collectionStartDate');
            if (null !== $value) {
                $rangeQuery = new Range($field);
                $filterDate = new \DateTime($value);
                $rangeQuery->addField($field, ['gte' => $filterDate->format('Y-m-d H:i:s')]);
                $filterQuery->addFilter($rangeQuery);
            }

            $field = 'collectionEndDate';
            $value = $this->getValueFromFilterRegex($filter[0], $field);
            if (null !== $value) {
                $rangeQuery = new Range($field);
                $filterDate = new \DateTime($value);
                $rangeQuery->addField($field, ['lte' => $filterDate->format('Y-m-d H:i:s')]);
                $filterQuery->addFilter($rangeQuery);
            }

            $field = 'researchGroup.id';
            $value = $this->getValueFromFilterRegex($filter[0], $field);
            if (null !== $value) {
                $valuesArray = json_decode($value);
                if (count($valuesArray) > 0) {
                    $nestedQuery = new Nested();
                    $nestedQuery->setPath('researchGroup');
                    $termQuery = new Terms('researchGroup.id');
                    $termQuery->setTerms($valuesArray);
                    $nestedQuery->setQuery($termQuery);
                    $filterQuery->addFilter($nestedQuery);
                }
            }

            if ($filterQuery->count() > 0) {
                $mainQuery->addMust($filterQuery);
            }

            $userData = json_decode($userData, true);

            switch ($userData['geometrySearchMode'] ?? null) {
                case 'intersects':
                    $geometrySearchMode = GeoShapeProvided::RELATION_INTERSECT;
                    break;
                case 'within':
                    $geometrySearchMode = GeoShapeProvided::RELATION_WITHIN;
                    break;
                default:
                    $geometrySearchMode = GeoShapeProvided::RELATION_WITHIN;
            }

            $field = 'geometry';
            $value = $this->getValueFromFilterRegex($filter[0], $field);
            if (null !== $value) {
                $geoJson = json_decode($value, true);
                if (isset($geoJson['geometry']['coordinates'])) {
                    $geoQuery = new GeoShapeProvided(
                        'simpleGeometry',
                        $geoJson['geometry']['coordinates'],
                        GeoShapeProvided::TYPE_POLYGON
                    );
                    $geoQuery->setRelation($geometrySearchMode);

                    $mainQuery->addFilter($geoQuery);
                }
            }

            $query->setQuery($mainQuery);
        }

        $find = $this->searchPelagosFinder->findHybridPaginated($query);

        /** @psalm-suppress PossiblyNullOperand */
        $page = (int) ($skip / $take) + 1;

        $find->setMaxPerPage($take);
        $find->setCurrentPage($page);

        $results = $find->getCurrentPageResults();

        $items = [];

        foreach ($results as $result) {
            array_push($items, $result->getResult()->getData());
        }

        if (is_array($group) && !empty($group)) {
            $groupItems = array_map(function ($item) {
                return [
                    'key' => $item,
                ];
            }, array_column(DatasetLifecycleStatus::cases(), 'value'));

            $data = [
                'data' => $groupItems,
                'totalCount' => -1,
                'summary' => [],
            ];
        } else {
            $data = [
                'data' => $items,
                'summary' => [],
            ];
            if ($requireTotalCount) {
                $data['totalCount'] = $find->getNbResults();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Get all datasets as GeoJson.
     */
    #[Route(path: '/map/geojson', name: 'pelagos_map_all_geojson')]
    public function getDatasetsAsGeoJson(): Response
    {
        $query = new Query();
        $result = $this->searchPelagosFinder->findHybridPaginated($query);
        $result->setMaxPerPage(500);

        $features = [];

        for ($index = 1; $index <= $result->getNbPages(); ++$index) {
            $result->setCurrentPage($index);
            $transformed = $result->getCurrentPageResults();
            foreach ($transformed as $item) {
                $data = $item->getResult()->getData();
                if (array_key_exists('geometry', $data)) {
                    $feature = $data['geometry'];
                    $features[] = json_decode($feature);
                }
            }
        }

        $geoJson = [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];

        return new JsonResponse($geoJson);
    }

    /**
     * Parse the parameters.
     *
     * @param mixed $params the parameters coming from the request
     * @param bool  $assoc  whether to return the result as an associative array or not
     */
    private function getParseParams(mixed $params, bool $assoc = false): mixed
    {
        $result = null;
        if (is_array($params)) {
            $result = [];
            foreach ($params as $key => $value) {
                $result[$key] = json_decode($params[$key], $assoc);
                if (null === $result[$key]) {
                    $result[$key] = $params[$key];
                }
            }
        } else {
            $result = $params;
        }

        return $result;
    }
}

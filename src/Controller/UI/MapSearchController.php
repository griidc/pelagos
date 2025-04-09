<?php

namespace App\Controller\UI;

use App\Enum\DatasetLifecycleStatus;
use App\Util\ExpressionParser;
use Elastica\Query;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Range;
use Elastica\Query\Term;
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

    #[Route('/map', name: 'app_map_search')]
    public function index(): Response
    {
        return $this->render('MapSearch/index.html.twig');
    }

    #[Route('/map/search', name: 'app_map_search_search')]
    public function search(
        #[MapQueryParameter] ?int $take = 20,
        #[MapQueryParameter] ?int $skip = 0,
        #[MapQueryParameter] ?array $filter = [],
        #[MapQueryParameter] ?array $group = [],
        #[MapQueryParameter] ?array $sort = [],
        #[MapQueryParameter] ?string $searchOperation = 'contains',
        #[MapQueryParameter] ?bool $requireTotalCount = false,
    ): Response {
        $query = new Query();

        if (is_array($sort) && !empty($sort)) {
            $sort = $this->getParseParams($sort, true);
            foreach ($sort as $item) {
                $query->addSort([$item[0]['selector'] => ['order' => $item[0]['desc'] ? 'DESC' : 'ASC']]);
            }
        }

        if (null !== $filter && [] !== $filter) {
            $filters = json_decode($filter[0], true);

            $searchFilter = new BoolQuery();
            $filterQuery = new BoolQuery();

            $lastOperation = 'or';

            if (3 === count($filters) and is_string($filters[0]) && is_string($filters[1]) && is_string($filters[2])) {
                $fieldName = $filters[0];
                $fieldOperation = $filters[1];
                $fieldValue = $filters[2];

                switch ($fieldOperation) {
                    case '=':
                        $filterQuery = new Term();
                        $filterQuery->setTerm($fieldName, $fieldValue);
                        break;
                    case '<=':
                        $filterQuery = new Range($fieldName);
                        $filterDate = new \DateTime($fieldValue);
                        $filterQuery->addField($fieldName, ['lte' => $filterDate->format('Y-m-d H:i:s')]);
                        break;
                    case '<':
                        $filterQuery = new Range($fieldName);
                        $filterDate = new \DateTime($fieldValue);
                        $filterQuery->addField($fieldName, ['lt' => $filterDate->format('Y-m-d H:i:s')]);
                        break;
                    case '>=':
                        $filterQuery = new Range($fieldName);
                        $filterDate = new \DateTime($fieldValue);
                        $filterQuery->addField($fieldName, ['gte' => $filterDate->format('Y-m-d H:i:s')]);
                        break;
                    default:
                        throw new \InvalidArgumentException('Invalid filter operation');
                }
                $searchFilter->addShould($filterQuery);
            } else {
                foreach ($filters as $filter) {
                    if (is_array($filter)) {
                        $filterQuery = $this->filterArrayToQuery($filter);
                    } else {
                        switch ($filter) {
                            case 'and':
                                $searchFilter->addMust($filterQuery);
                                $lastOperation = 'and';
                                break;
                            case 'or':
                                $searchFilter->addShould($filterQuery);
                                $lastOperation = 'or';
                                break;
                            case '=':
                                $searchFilter->addShould($filterQuery);
                                break;
                            default:
                                throw new \InvalidArgumentException('Invalid filter operation');
                        }
                    }

                    if ('and' === $lastOperation) {
                        $searchFilter->addMust($filterQuery);
                    } else {
                        $searchFilter->addShould($filterQuery);
                    }
                }
            }

            $query->setQuery($searchFilter);
        }

        $find = $this->searchPelagosFinder->findHybridPaginated($query);

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
                    'items' => 12,
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
     * Transform the filter array to a query.
     *
     * @param array $filters
     */
    private function filterArrayToQuery(array $filters): AbstractQuery
    {
        $query = new BoolQuery();

        if (is_array($filters[0])) {
            $searchFilter = new BoolQuery();
            $filterQuery = new BoolQuery();
            $lastOperation = 'or';
            foreach ($filters as $filter) {
                if (is_array($filter)) {
                    $filterQuery = $this->filterArrayToQuery($filter);
                } else {
                    switch ($filter) {
                        case 'and':
                            $searchFilter->addMust($filterQuery);
                            $lastOperation = 'and';
                            break;
                        case 'or':
                            $searchFilter->addShould($filterQuery);
                            $lastOperation = 'or';
                            break;
                        case 'lte':
                            dd($filterQuery);
                            // $filterQuery = new Range($filter[0]);
                            // $searchFilter->addShould($filterQuery);
                            // $lastOperation = 'or';
                            break;
                        default:
                            throw new \InvalidArgumentException('Invalid filter operation');
                    }
                }

                if ('and' === $lastOperation) {
                    $searchFilter->addMust($filterQuery);
                } else {
                    $searchFilter->addShould($filterQuery);
                }
            }

            return $searchFilter;
        }

        $fieldName = $filters[0];
        $fieldOperation = $filters[1];
        $fieldValue = $filters[2];

        switch ($fieldOperation) {
            case '=':
                $query = new Term();
                $query->setTerm($fieldName, $fieldValue);
                break;
            case '<=':
                $filterQuery = new Range($fieldName);
                $filterDate = new \DateTime($fieldValue);
                $filterQuery->addField($fieldName, ['lte' => $filterDate->format('Y-m-d H:i:s')]);
                break;
            case '<':
                $filterQuery = new Range($fieldName);
                $filterDate = new \DateTime($fieldValue);
                $filterQuery->addField($fieldName, ['lt' => $filterDate->format('Y-m-d H:i:s')]);
                break;
            case '>=':
                $filterQuery = new Range($fieldName);
                $filterDate = new \DateTime($fieldValue);
                $filterQuery->addField($fieldName, ['gte' => $filterDate->format('Y-m-d H:i:s')]);
                break;
            case 'contains':
                $query = new Query\MatchPhrase($fieldName, $fieldValue);
                break;
            default:
                throw new \InvalidArgumentException('Invalid filter operation');
        }

        return $query;
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

<?php

namespace App\Controller\UI;

use App\Enum\DatasetLifecycleStatus;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class MapSearchController extends AbstractController
{
    #[Route('/map', name: 'app_map_search')]
    public function index(): Response
    {
        return $this->render('MapSearch/index.html.twig');
    }

    #[Route('/map/search', name: 'app_map_search_search')]
    public function search(
        TransformedFinder $searchPelagosFinder,
        #[MapQueryParameter] ?int $take =20,
        #[MapQueryParameter] ?int $skip = 0,
        #[MapQueryParameter] ?array $filter = [],
        #[MapQueryParameter] ?array $group = [],
        #[MapQueryParameter] ?array $sort = [],
        #[MapQueryParameter] ?string $searchOperation = 'contains',
        #[MapQueryParameter] ?bool $requireTotalCount = false,
    ): Response {
        $query = new Query();

        if (is_array($sort) && !empty($sort)) {
            $sort = $this->GetParseParams($sort, true);
            foreach ($sort as $item) {
                $query->addSort([$item[0]['selector'] => ['order' => $item[0]['desc'] ? 'DESC' : 'ASC']]);
            }
        }

        if (null !== $filter && [] !== $filter) {
            $filters = json_decode($filter[0]);

            $boolQuery = new BoolQuery();

            foreach ($filters as $filter) {
                if (is_array($filter)) {
                    $matchQuery = new Query\MatchPhrase($filter[0], $filter[2]);
                    $boolQuery->addShould($matchQuery);
                }
            }

            $query->setQuery($boolQuery);
        }

        $find = $searchPelagosFinder->findHybridPaginated($query);

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

    private function GetParseParams(mixed $params, bool $assoc = false): mixed
    {
        $result = NULL;
        if (is_array($params)) {
            $result = array();
            foreach ($params as $key => $value) {
                $result[$key] = json_decode($params[$key], $assoc);
                if ($result[$key] === NULL) {
                    $result[$key] = $params[$key];
                }
            }
        }
        else {
            $result = $params;
        }
        return $result;
    }
}

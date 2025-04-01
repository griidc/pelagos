<?php

namespace App\Controller\UI;

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
        #[MapQueryParameter] int $skip,
        #[MapQueryParameter] int $take,
        #[MapQueryParameter] ?array $filter = [],
        #[MapQueryParameter] ?array $group = [],
        #[MapQueryParameter] ?array $sort = [],
        #[MapQueryParameter] ?string $searchOperation = 'contains',
        #[MapQueryParameter] ?bool $requireTotalCount = true,
    ): Response {
        $query = new Query();

        if (is_array($sort) && !empty($sort)) {
            $sortArray = json_decode($sort[0]);
            $sortObject = $sortArray['0'];
            $query->addSort([$sortObject->selector => ['order' => $sortObject->desc ? 'DESC' : 'ASC']]);
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

        $data = [
            'data' => $items,
            'totalCount' => $find->getNbResults(),
            'summary' => [],
        ];

        return new JsonResponse($data);
    }
}

<?php

namespace App\Search;

use Elastica\Query\Match;
use Elastica\Query\MatchQuery;
use FOS\ElasticaBundle\Repository;

class SearchRepository extends Repository
{
    // public function search(string $searchTerm, int $page = 1, int $limit = 48) : ?array
    // {
    //     if ($searchTerm) {
    //         $fieldQuery = new MatchQuery();
    //         $fieldQuery->setFieldQuery('name', $searchTerm);
    //         $items     = $this->findPaginated($fieldQuery);
    //         $items->setMaxPerPage($limit);
    //         $items->setCurrentPage($page);

    //         return $items;
    //     }

    //     return null;
    // }
}

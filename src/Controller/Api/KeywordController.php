<?php

namespace App\Controller\Api;

use App\Enum\KeywordType;
use App\Repository\KeywordRepository;
use App\Search\Elastica\ExtendedTransformedFinder;
use App\Util\KeywordUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Elastica\Aggregation\Terms as AggregationTerms;
use Elastica\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class KeywordController extends AbstractController
{
    #[Route(path: '/api/keywords/level2/{type}', name: 'api_keywords_level2')]
    public function getLevel2Keywords(string $type, KeywordRepository $keywordRepository, KeywordUtil $keywordUtil, ExtendedTransformedFinder $searchPelagosFinder): Response
    {
        $keywordType = KeywordType::tryFrom($type);
        $keywords = $keywordRepository->getKeywordsByType($keywordType);
        $level2Keywords = new ArrayCollection($keywordUtil->getKeywordsByLevel($keywords, 2));

        $query = new Query();

        $keywordAgg = new AggregationTerms('levelTwoKeywordTerms');
        $keywordAgg->setField('anzsrcLevelTwo');
        $keywordAgg->setSize(99999);
        $query->addAggregation($keywordAgg);

        $results = $searchPelagosFinder->getSearch()->search($query);
        $buckets = $results->getAggregation('levelTwoKeywordTerms')['buckets'];

        $data = [];

        foreach ($buckets as $bucket) {
           $keyword = $level2Keywords->filter(function ($keyword) use ($bucket) {
               return $keyword->getShortDisplayPath() === $bucket['key'];
           })->first();

           if (!$keyword) {
               continue;
           }

           $data[] = [
                'id' => $keyword->getId(),
                'type' => $keyword->getType(),
                'shortDisplayPath' => $keyword->getShortDisplayPath(),
                'displayPath' => $keyword->getDisplayPath(),
                'label' => $keyword->getLabel(),
                'count' => $bucket['doc_count'],
            ];
        }

        return new JsonResponse(data: $data);
    }
}

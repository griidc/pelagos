<?php

namespace App\Controller\Api;

use App\Enum\KeywordType;
use App\Repository\KeywordRepository;
use App\Util\KeywordUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class KeywordController extends AbstractController
{
    #[Route(path: '/api/keywords/level2/{type}', name: 'api_keywords_level2')]
    public function getLevel2Keywords(KeywordRepository $keywordRepository, KeywordUtil $keywordUtil, SerializerInterface $serializer, string $type): Response
    {
        $keywordType = KeywordType::tryFrom($type);
        $keywords = $keywordRepository->getKeywords($keywordType);
        $level2Keywords = $keywordUtil->getKeywordsByLevel($keywords, 2);

        $context = (new ObjectNormalizerContextBuilder())
        ->withAttributes(['id', 'type', 'shortDisplayPath', 'displayPath', 'label']);

        $json = $serializer->serialize($level2Keywords, 'json', $context->toArray());

        $data = json_decode($json, true);

        return new JsonResponse(
            data: $data,
            json: true
        );
    }
}

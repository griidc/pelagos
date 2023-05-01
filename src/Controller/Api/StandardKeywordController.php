<?php

namespace App\Controller\Api;

use App\Enum\KeywordType;
use App\Repository\KeywordRepository;
use App\Util\JsonSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StandardKeywordController extends AbstractController
{
    #[Route('/api/standard/keyword', name: 'app_api_standard_keyword')]
    public function index(Request $request, KeywordRepository $keywordRepository, JsonSerializer $jsonSerializer): Response
    {
        $type =  KeywordType::tryFrom($request->query->get('type') ?? '') ?? KeywordType::cases();
        $keywords = $keywordRepository->findBy(
            criteria: ['type' => $type],
            orderBy: ['label' => 'ASC']
        );

        return $jsonSerializer->serialize($keywords, ['id', 'api'])->createJsonResponse();
    }
}

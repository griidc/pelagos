<?php

namespace App\Controller\Api;

use App\Enum\KeywordType;
use App\Repository\KeywordRepository;
use App\Util\JsonSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StandardKeywordController extends AbstractController
{
    #[Route('/api/standard/keyword', name: 'app_api_standard_keyword')]
    public function index(Request $request, KeywordRepository $keywordRepository): Response
    {
        $type = $request->query->get('type') ?? KeywordType::TYPE_ANZSRC;        
        $keywords = $keywordRepository->findBy(
            ['type' => $type]
        );
      
        $data = [];
        $parentIds = $request->query->get('parentIds');
        $parentId = $parentIds[0] ?? '';
        $regex = '/^' . $parentId . '\d\d$/i';

        foreach ($keywords as $keyword) {
            $json = $keyword->getJson();

            if (empty($json) or !preg_match($regex, $json["notation"])) {
                continue;
            }

            $data[] = [
                "notation" => $json["notation"],
                "label" => $json["prefLabel"]["_value"],
                "hasItems" => !(strlen($parentId) == 4),
                "parentId" => $parentId,
            ];
        }

        usort($data, function($a, $b) {
            return strcmp($a["notation"], $b["notation"]);
        });

        return new JsonResponse($data);
    }
}
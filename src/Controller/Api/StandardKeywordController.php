<?php

namespace App\Controller\Api;

use App\Enum\KeywordType;
use App\Repository\KeywordRepository;
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
        $type = $request->query->get('type') ?? [KeywordType::TYPE_GCMD, KeywordType::TYPE_ANZSRC];
        $keywords = $keywordRepository->findBy(
            ['type' => $type]
        );

        $data = [];

        foreach ($keywords as $keyword) {
            $uri = $keyword->getReferenceUri();
            $label = $keyword->getLabel();
            $parentId = $keyword->getParentUri();
            $definition = $keyword->getDefinition();
            $displayPath = $keyword->getDisplayPath();

            $data[] = [
                'key' => $uri,
                'label' => $label,
                'hasItems' => !(empty($parentId)),
                'parent' => $parentId,
                'definition' => $definition,
                'displayPath' => $displayPath,
            ];
        }

        usort($data, function ($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        return new JsonResponse($data);
    }
}

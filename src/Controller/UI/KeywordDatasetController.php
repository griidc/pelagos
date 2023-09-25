<?php

namespace App\Controller\UI;

use App\Repository\DatasetRepository;
use App\Util\JsonSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class KeywordDatasetController extends AbstractController
{
    #[Route('/keywordToDataset', name: 'pelagos_app_ui_keyword_dataset')]
    public function index(): Response
    {
        return $this->render('KeywordDataset/list.html.twig', [
        ]);
    }

    #[Route('/api/KeywordDataset', name: 'pelagos_app_api_keyword_dataset')]
    public function getList(DatasetRepository $datasetRepository, JsonSerializer $jsonSerializer): Response
    {
        $datasets = $datasetRepository->getListOfApprovedDatasetWithoutKeywords();

        return $jsonSerializer->serialize(
            data: $datasets,
        )->createJsonResponse();
    }
}

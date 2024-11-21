<?php

namespace App\Controller\UI;

use App\Entity\FundingCycle;
use App\Repository\InformationProductRepository;
use App\Repository\DatasetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

class FundingCycleLandController extends AbstractController
{
    #[Route('/funding-cycle/about/{fundingCycle}', name: 'app_funding_cycle_land')]
    public function index(FundingCycle $fundingCycle, InformationProductRepository $informationProductRepository, DatasetRepository $datasetRepository, #[MapQueryParameter] ?int $tab = null): Response
    {
        $informationProducts = $informationProductRepository->findByFundingCycle($fundingCycle);

        $publications = [];
        foreach ($fundingCycle->getDatasets() as $dataset) {
            foreach ($dataset->getPublications() as $datasetPublication) {
                $publications[] = $datasetPublication;
            }
        }

        // $datasets = $datasetRepository->getDatasetsByFundingCycle($fundingCycle);

        return $this->render(
            'FundingCycleLand/index.html.twig',
            [
                'fundingCycle' => $fundingCycle,
                'informationProducts' => $informationProducts,
                'publications' => $publications,
                'tab' => $tab,
            ]
        );
    }
}

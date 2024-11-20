<?php

namespace App\Controller\UI;

use App\Entity\FundingCycle;
use App\Repository\InformationProductRepository;
use App\Repository\DatasetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FundingCycleLandController extends AbstractController
{
    private $informationProductRepository;
    private $datasetRepository;

    public function __construct(InformationProductRepository $informationProductRepository, DatasetRepository $datasetRepository)
    {
        $this->informationProductRepository = $informationProductRepository;
        $this->datasetRepository = $datasetRepository;
    }

    #[Route('/funding-cycle/about/{fundingCycle}', name: 'app_funding_cycle_land')]
    public function index(FundingCycle $fundingCycle, string $tab = null): Response
    {
        $tab = $this->container->get('request_stack')->getCurrentRequest()->query->get('tab', $tab);

        $researchGroupList = [];
        foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
            /* @var $researchGroup \App\Entity\ResearchGroup */
            $researchGroupList[] = $researchGroup->getId();
        }

        $informationProducts = $this->informationProductRepository->findByResearchGroupIds($researchGroupList);
        $datasets = $this->datasetRepository->getDatasetsByFundingCycle($fundingCycle->getId());

        return $this->render(
            'FundingCycleLand/index.html.twig',
            [
                'fundingCycle' => $fundingCycle,
                'researchGroupList' => $researchGroupList,
                'informationProducts' => $informationProducts,
                'datasets' => $datasets,
                'tab' => $tab,
            ]
        );
    }
}

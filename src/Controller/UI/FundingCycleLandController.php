<?php

namespace App\Controller\UI;

use App\Entity\FundingCycle;
use App\Repository\InformationProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FundingCycleLandController extends AbstractController
{
    #[Route('/funding-cycle/about/{fundingCycle}', name: 'app_funding_cycle_land')]
    public function index(FundingCycle $fundingCycle, InformationProductRepository $informationProductRepository): Response
    {
        $informationProducts = $informationProductRepository->findByFundingCycle($fundingCycle);

        return $this->render(
            'LandingPages/funding-cycle-land.html.twig',
            [
                'fundingCycle' => $fundingCycle,
                'informationProducts' => $informationProducts,
            ]
        );
    }
}

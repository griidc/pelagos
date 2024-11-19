<?php

namespace App\Controller\UI;

use App\Entity\FundingCycle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FundingCycleLandController extends AbstractController
{
    #[Route('/funding-cycle/about/{fundingCycle}', name: 'app_funding_cycle_land')]
    public function index(FundingCycle $fundingCycle): Response
    {
        return $this->render(
            'FundingCycleLand/index.html.twig',
            [
                'fundingCycle' => $fundingCycle,
            ]
        );
    }
}

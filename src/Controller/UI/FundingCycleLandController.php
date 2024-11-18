<?php

namespace App\Controller\UI;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FundingCycleLandController extends AbstractController
{
    #[Route('/fcland', name: 'app_funding_cycle_land')]
    public function index(): Response
    {
        return $this->render('FundingCycleLand/index.html.twig');
    }
}

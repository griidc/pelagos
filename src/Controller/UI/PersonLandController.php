<?php

namespace App\Controller\UI;

use App\Entity\Person;
use App\Repository\InformationProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonLandController extends AbstractController
{
    #[Route('/person/about/{person}', name: 'app_person_land')]
    public function index(Person $person, InformationProductRepository $informationProductRepository): Response
    {
        // $informationProducts = $informationProductRepository->findByFundingCycle($fundingCycle);

        return $this->render(
            'LandingPages/person-land.html.twig',
            [
                'person' => $person,
                // 'informationProducts' => $informationProducts,
            ]
        );
    }
}

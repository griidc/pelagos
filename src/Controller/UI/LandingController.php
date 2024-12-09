<?php

namespace App\Controller\UI;

use App\Entity\FundingCycle;
use App\Entity\Person;
use App\Entity\ResearchGroup;
use App\Repository\InformationProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LandingController extends AbstractController
{
    #[Route('/funding-cycle/about/{fundingCycle}', name: 'app_funding_cycle_land')]
    public function fundingCycleLand(FundingCycle $fundingCycle, InformationProductRepository $informationProductRepository): Response
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

    #[Route('/person/about/{person}', name: 'app_person_land')]
    public function personLand(Person $person, InformationProductRepository $informationProductRepository): Response
    {

        $informationProducts = $informationProductRepository->findByPerson($person);

        return $this->render(
            'LandingPages/person-land.html.twig',
            [
                'person' => $person,
                'informationProducts' => $informationProducts,
            ]
        );
    }

    #[Route('/researchgroup/about/{researchGroup}', name: 'app_research_group_land')]
    public function researchGroupLand(ResearchGroup $researchGroup, InformationProductRepository $informationProductRepository): Response
    {

        $informationProducts = $informationProductRepository->findOneByResearchGroupId($researchGroup->getId());

        return $this->render(
            'LandingPages/research-group-land.html.twig',
            [
                'researchGroup' => $researchGroup,
                'informationProducts' => $informationProducts,
            ]
        );
    }
}

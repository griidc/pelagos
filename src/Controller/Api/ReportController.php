<?php

namespace App\Controller\Api;

use App\Repository\ResearchGroupRepository;
use App\Util\FundingOrgFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ReportController extends AbstractController
{
    #[Route(path: '/api/stuff')]
    public function getStuff(ResearchGroupRepository $researchGroupRepository, FundingOrgFilter $fundingOrgFilter, SerializerInterface $serialzer) : Response
    {
        $researchGroupsArray = $fundingOrgFilter->getResearchGroupsIdArray();

        $researchGroups = $researchGroupRepository->findBy($researchGroupsArray);

        $data = $serialzer->serialize($researchGroups, 'json', ['groups' => 'grp-dp-report']);

        // dd($data);


        return new Response($data, 200, ['Content-Type' => 'text/json']);
    }
}

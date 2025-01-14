<?php

namespace App\Controller\Api;

use App\Entity\ResearchGroup;
use App\Repository\FundingOrganizationRepository;
use App\Repository\ResearchGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
class ReportController extends AbstractController
{
    #[Route(path: '/api/stuff')]
    public function getStuff(ResearchGroupRepository $researchGroupRepository,  FundingOrganizationRepository $fundingOrganizationRepository, SerializerInterface $serialzer) : Response
    {
        $fundingOrganization = $fundingOrganizationRepository->findOneBy(['shortName' => 'NAS']);

        $researchGroupIds = [];
        foreach ($fundingOrganization->getFundingCycles() as $fundingCycle) {
            foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
                $researchGroupIds[] = $researchGroup->getId();
            }
        }

        $researchGroups = $researchGroupRepository->findBy(['id' => $researchGroupIds], ['name' => 'ASC']);

        usort($researchGroups, function(ResearchGroup $a, ResearchGroup $b) {
            return $a->getFundingCycle()->getName() <=> $b->getFundingCycle()->getName();
        });

        $data = $serialzer->serialize($researchGroups, 'csv',
            [
                'groups' => 'grp-dp-report',
                'csv_headers' => [
                    'Funding Cycle' => 'fundingCycle.name',
                    'ResearchGroupName',
                    'approvedDifsCount',
                    'submittedDatasets',
                    'availableDatasets',
                    'restrictedDataset',
                    'peopleCount'
                ],
                'output_utf8_bom' => true,
            ]);

            $csvFilename = 'DatasetMonitoringReport-' .
            (new \DateTime('now'))->format('Ymd\THis') .
            '.csv';

            dd($data);

        $response = new Response($data);

        $response->headers->set(
            'Content-disposition',
            HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $csvFilename)
        );
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Encoding', 'UTF-8');

        return $response;
    }
}

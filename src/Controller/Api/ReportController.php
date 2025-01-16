<?php

namespace App\Controller\Api;

<<<<<<< HEAD
use App\Entity\FundingOrganization;
use App\Entity\ResearchGroup;
use App\Repository\FundingOrganizationRepository;
use App\Repository\PersonResearchGroupRepository;
use App\Repository\ResearchGroupRepository;
=======
use App\Entity\ResearchGroup;
use App\Repository\FundingOrganizationRepository;
use App\Repository\ResearchGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
>>>>>>> b88d625f4 (Working CSV)
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
class ReportController extends AbstractController
{
<<<<<<< HEAD
    #[Route(path: '/api/grp-datasets-people-report', name: 'pelagos_api_grp_datasets_people_report', methods: ['GET'])]
    public function getGrpDatasetAndPeopleReport(ResearchGroupRepository $researchGroupRepository, FundingOrganizationRepository $fundingOrganizationRepository, SerializerInterface $serialzer): Response
=======
    #[Route(path: '/api/stuff')]
    public function getStuff(ResearchGroupRepository $researchGroupRepository,  FundingOrganizationRepository $fundingOrganizationRepository, SerializerInterface $serialzer) : Response
>>>>>>> b88d625f4 (Working CSV)
    {
        $fundingOrganization = $fundingOrganizationRepository->findOneBy(['shortName' => 'NAS']);

        $researchGroupIds = [];
<<<<<<< HEAD
        if ($fundingOrganization instanceof FundingOrganization) {
            foreach ($fundingOrganization->getFundingCycles() as $fundingCycle) {
                foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
                    $researchGroupIds[] = $researchGroup->getId();
                }
=======
        foreach ($fundingOrganization->getFundingCycles() as $fundingCycle) {
            foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
                $researchGroupIds[] = $researchGroup->getId();
>>>>>>> b88d625f4 (Working CSV)
            }
        }

        $researchGroups = $researchGroupRepository->findBy(['id' => $researchGroupIds], ['name' => 'ASC']);

<<<<<<< HEAD
        usort($researchGroups, function (ResearchGroup $a, ResearchGroup $b) {
            return $a->getFundingCycle()->getName() <=> $b->getFundingCycle()->getName();
        });

        $data = $serialzer->serialize(
            $researchGroups,
            'csv',
            [
                'groups' => 'grp-dp-report',
                'csv_headers' => [
                    'fundingCycle.name',
=======
        usort($researchGroups, function(ResearchGroup $a, ResearchGroup $b) {
            return $a->getFundingCycle()->getName() <=> $b->getFundingCycle()->getName();
        });

        $data = $serialzer->serialize($researchGroups, 'csv',
            [
                'groups' => 'grp-dp-report',
                'csv_headers' => [
                    'Funding Cycle' => 'fundingCycle.name',
>>>>>>> b88d625f4 (Working CSV)
                    'ResearchGroupName',
                    'approvedDifsCount',
                    'submittedDatasets',
                    'availableDatasets',
                    'restrictedDataset',
<<<<<<< HEAD
                    'peopleCount',
                ],
                'output_utf8_bom' => true,
            ]
        );

        $csvFilename = 'GRP-Datasets-People-Report-' .
        (new \DateTime('now'))->format('Ymd\THis') .
        '.csv';

        $response = new Response($data);

        $response->headers->set(
            'Content-disposition',
            HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $csvFilename)
        );
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Encoding', 'UTF-8');

        return $response;
    }

    #[Route(path: '/api/grp-datasets-keywords-report', name: 'pelagos_api_grp_datasets_keywords_report', methods: ['GET'])]
    public function getGrpDatasetAndKeywordsReport(FundingOrganizationRepository $fundingOrganizationRepository, SerializerInterface $serialzer): Response
    {
        $fundingOrganization = $fundingOrganizationRepository->findOneBy(['shortName' => 'NAS']);

        $datasets = $fundingOrganization->getDatasets();

        $data = $serialzer->serialize(
            $datasets,
            'csv',
            [
                'groups' => 'grp-dk-report',
                'csv_headers' => [
                    'researchGroup.fundingCycle.name',
                    'researchGroup.ResearchGroupName',
                    'udi',
                    'title',
                    'doi',
                ],
                'output_utf8_bom' => true,
                'enable_max_depth' => true,
            ]
        );

        $csvFilename = 'GRP-Dataset-Keywords-Report-' .
        (new \DateTime('now'))->format('Ymd\THis') .
        '.csv';

        $response = new Response($data);

        $response->headers->set(
            'Content-disposition',
            HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $csvFilename)
        );
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Encoding', 'UTF-8');

        return $response;
    }

    #[Route(path: '/api/grp-people-accounts-report', name: 'pelagos_api_grp_accounts_people_report', methods: ['GET'])]
    public function getGrpPeopleAndAccountsReport(
        FundingOrganizationRepository $fundingOrganizationRepository,
        PersonResearchGroupRepository $personResearchGroupRepository,
        SerializerInterface $serializer,
    ): Response {
        $fundingOrganization = $fundingOrganizationRepository->findOneBy(['shortName' => 'NAS']);

        $researchGroupIds = [];
        if ($fundingOrganization instanceof FundingOrganization) {
            foreach ($fundingOrganization->getFundingCycles() as $fundingCycle) {
                foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
                    $researchGroupIds[] = $researchGroup->getId();
                }
            }
        }

        $personResearchGroups = $personResearchGroupRepository->findBy(['researchGroup' => $researchGroupIds]);

        $data = $serializer->serialize($personResearchGroups, 'json',
            [
                'groups' => ['grp-people-accounts-report'],
                'enable_max_depth' => true,
            ]);

        return new Response($data, 200, ['Content-Type' => 'application/json', 'Content-Encoding' => 'UTF-8']);

        $csvFilename = 'GRP-People-Accounts-Report-' .
        (new \DateTime('now'))->format('Ymd\THis') .
        '.csv';
=======
                    'peopleCount'
                ],
                'output_utf8_bom' => true,
            ]);

            $csvFilename = 'DatasetMonitoringReport-' .
            (new \DateTime('now'))->format('Ymd\THis') .
            '.csv';

            dd($data);
>>>>>>> b88d625f4 (Working CSV)

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

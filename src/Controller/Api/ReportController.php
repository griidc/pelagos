<?php

namespace App\Controller\Api;


use App\Repository\FundingOrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class ReportController extends AbstractController
{
    #[Route(path: '/api/grp-people-accounts-report', name: 'pelagos_api_grp_accounts_people_report', methods: ['GET'])]
    public function getGrpPeopleAndAccountsReport(FundingOrganizationRepository $fundingOrganizationRepository): Response
    {
        $fundingOrganization = $fundingOrganizationRepository->findOneBy(['shortName' => 'NAS']);
        $fundingCycles = $fundingOrganization->getFundingCycles();

        foreach ($fundingCycles as $fundingCycle) {
            $researchGroups = $fundingCycle->getResearchGroups();
            foreach ($researchGroups as $researchGroup) {
                $people = $researchGroup->getPeople();
                foreach ($people as $person) {
                    $data[] = [
                        'Funding Cycle' => $fundingCycle->getName(),
                        'Research Group' => $researchGroup->getName(),
                        'Last Name' => $person->getLastName(),
                        'First Name' => $person->getFirstName(),
                        'Account' => $person->getAccount() ? True : False,
                        'Account Creation' => $person->getAccount()?->getCreationTimeStamp()->format('Y-m-d H:i:s'),
                    ];
                }
            }
        }

        var_dump($data);
        die();

        $csvFilename = 'GRP-People-Accounts-Report-' .
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
}

<?php

namespace App\Controller\Api;

use App\Entity\Account;
use App\Entity\Dataset;
use App\Entity\FundingOrganization;
use App\Entity\PersonResearchGroup;
use App\Entity\ResearchGroup;
use App\Enum\DatasetLifecycleStatus;
use App\Repository\DatasetRepository;
use App\Repository\FundingOrganizationRepository;
use App\Repository\PersonResearchGroupRepository;
use App\Repository\ResearchGroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Context\Encoder\CsvEncoderContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class ReportController extends AbstractController
{
    /**
     * Datasets and People Report.
     */
    #[Route(path: '/api/grp-datasets-people-report', name: 'pelagos_api_grp_datasets_people_report', methods: ['GET'])]
    #[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
    public function getGrpDatasetAndPeopleReport(
        ResearchGroupRepository $researchGroupRepository,
        FundingOrganizationRepository $fundingOrganizationRepository,
        SerializerInterface $serialzer,
    ): Response {
        $fundingOrganization = $fundingOrganizationRepository->findOneBy(['shortName' => 'NAS']);

        $researchGroupIds = $this->getResearchGroupsIdsByFundingOrganization($fundingOrganization);

        $researchGroups = $researchGroupRepository->findBy(['id' => $researchGroupIds], ['name' => 'ASC']);

        usort($researchGroups, function (ResearchGroup $a, ResearchGroup $b) {
            return $a->getFundingCycleName() <=> $b->getFundingCycleName();
        });

        $contextBuilder = (new ObjectNormalizerContextBuilder())
        ->withGroups(['grp-dp-report']);

        $contextBuilder = (new CsvEncoderContextBuilder())
        ->withContext($contextBuilder)
        ->withOutputUtf8Bom(true)
        ->withHeaders([
            'fundingCycleName',
            'researchGroupName',
            'approvedDifsCount',
            'submittedDatasets',
            'availableDatasets',
            'restrictedDataset',
            'peopleCount',
        ]);

        $data = $serialzer->serialize($researchGroups, 'csv', $contextBuilder->toArray());

        $csvFilename = 'GRP-Datasets-People-Report-' .
        (new \DateTime('now'))->format('Ymd\THis') .
        '.csv';

        return $this->createCsvResponse($data, $csvFilename);
    }

    /**
     * Datasets and Keywords Report.
     */
    #[Route(path: '/api/grp-datasets-keywords-report', name: 'pelagos_api_grp_datasets_keywords_report', methods: ['GET'])]
    #[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
    public function getGrpDatasetAndKeywordsReport(
        FundingOrganizationRepository $fundingOrganizationRepository,
        SerializerInterface $serialzer,
    ): Response {
        $fundingOrganization = $fundingOrganizationRepository->findOneBy(['shortName' => 'NAS']);

        $datasets = $fundingOrganization?->getDatasets();

        $datasets = $datasets->filter(function (Dataset $dataset) {
            return DatasetLifecycleStatus::NONE !== $dataset->getDatasetLifecycleStatus();
        });

        $datasets = $datasets->toArray();

        usort($datasets, function (Dataset $a, Dataset $b) {
            return $a->getFundingCycleName() <=> $b->getFundingCycleName();
        });

        $contextBuilder = (new ObjectNormalizerContextBuilder())
        ->withGroups(['grp-dk-report']);

        $contextBuilder = (new CsvEncoderContextBuilder())
        ->withContext($contextBuilder)
        ->withOutputUtf8Bom(true)
        ->withHeaders([
            'fundingCycleName',
            'researchGroupName',
            'udi',
            'title',
            'doi',
        ]);

        $data = $serialzer->serialize($datasets, 'csv', $contextBuilder->toArray());

        $csvFilename = 'GRP-Dataset-Keywords-Report-' .
        (new \DateTime('now'))->format('Ymd\THis') .
        '.csv';

        return $this->createCsvResponse($data, $csvFilename);
    }

    /**
     * People and Accounts Report.
     */
    #[Route(path: '/api/grp-people-accounts-report', name: 'pelagos_api_grp_accounts_people_report', methods: ['GET'])]
    #[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
    public function getGrpPeopleAndAccountsReport(
        FundingOrganizationRepository $fundingOrganizationRepository,
        PersonResearchGroupRepository $personResearchGroupRepository,
        SerializerInterface $serializer,
    ): Response {
        $fundingOrganization = $fundingOrganizationRepository->findOneBy(['shortName' => 'NAS']);

        $researchGroupIds = $this->getResearchGroupsIdsByFundingOrganization($fundingOrganization);

        $personResearchGroups = $personResearchGroupRepository->findBy(['researchGroup' => $researchGroupIds]);

        usort($personResearchGroups, function (PersonResearchGroup $a, PersonResearchGroup $b) {
            return $a->getFundingCycleName() <=> $b->getFundingCycleName();
        });

        $contextBuilder = (new ObjectNormalizerContextBuilder())
        ->withGroups(['grp-people-accounts-report']);

        $contextBuilder = (new CsvEncoderContextBuilder())
        ->withContext($contextBuilder)
        ->withOutputUtf8Bom(true)
        ->withHeaders([
            'fundingCycleName',
            'researchGroupName',
        ]);

        $data = $serializer->serialize($personResearchGroups, 'csv', $contextBuilder->toArray());

        $csvFilename = 'GRP-People-Accounts-Report-' .
        (new \DateTime('now'))->format('Ymd\THis') .
        '.csv';

        return $this->createCsvResponse($data, $csvFilename);
    }

    /**
     * Create a CSV Response.
     *
     * @param mixed  $data        the CSV data
     * @param string $csvFilename the CSV filename
     */
    private function createCsvResponse(mixed $data, string $csvFilename): Response
    {
        $response = new Response($data);

        $response->headers->set(
            'Content-disposition',
            HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $csvFilename)
        );
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Encoding', 'UTF-8');

        return $response;
    }

    /**
     * Get Research Groups IDs by Funding Organization.
     */
    private function getResearchGroupsIdsByFundingOrganization(FundingOrganization $fundingOrganization): array
    {
        $researchGroupIds = [];
        foreach ($fundingOrganization->getFundingCycles() as $fundingCycle) {
            foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
                $researchGroupIds[] = $researchGroup->getId();
            }
        }

        return $researchGroupIds;
    }

    /**
     * Creates a CSV of the remotely-hosted datasets
     */
    #[Route(path: '/api/remotely-hosted-dataset-report', name: 'pelagos_api_remotely_hosted_report', methods: ['GET'])]
    #[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
    public function remotelyHostedReportCSV(DatasetRepository $datasetRepository, SerializerInterface $serializer): Response
    {
        $datasets = $datasetRepository->findAll();

        $array = [];
        foreach ($datasets as $dataset) {
            if ($dataset->IsRemotelyHosted()) {
                $array[] = $dataset;
            }
        }

        $contextBuilder = (new ObjectNormalizerContextBuilder())
        ->withGroups(['remotely-hosted-dataset-report']);

        $contextBuilder = (new CsvEncoderContextBuilder())
        ->withContext($contextBuilder)
        ->withOutputUtf8Bom(true)
        ->withAsCollection(false);

        $data = $serializer->serialize($array, 'csv', $contextBuilder->toArray());

        $csvFilename = 'RemotelyHostedDatasetReport-' .
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

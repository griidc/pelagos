<?php

namespace App\Controller\UI;

use App\Entity\Dataset;
use App\Repository\DatasetRepository;
use App\Util\FundingOrgFilter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface as SerializerInterface;

/**
 * GRP metadata export report generator.
 */
class GRPMetadataExportController extends ReportController
{
    /**
     * Holds DatasetRepository used to look up datasets.
     */
    private DatasetRepository $datasetRepository;

    /**
     * Class constructor for dependency injection.
     */
    public function __construct(entityManagerInterface $entityManager)
    {
        $this->datasetRepository = $entityManager->getRepository(Dataset::class);
    }

    /**
     * This is a parameterless report, so all is in the default action.
     */
    #[Route('/grp/export')]
    public function defaultAction(DatasetRepository $datasetRepository, SerializerInterface $serializer, FundingOrgFilter $fundingOrgFilter): Response
    {
        $fileName = 'GRP-Metadata-' . (new DateTime('now'))->format('Ymd\THis') . '.csv';

        $researchGroupIDs = [];
        if ($fundingOrgFilter->isActive()) {
            $researchGroupIDs = $fundingOrgFilter->getResearchGroupsIdArray();
        }

        $dataset = $datasetRepository->findBy(array('researchGroup' => $researchGroupIDs));
        $serializedData = $serializer->serialize($dataset, 'csv', [
            'groups' => ['export']
        ]);

        $response = new Response($serializedData);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=$fileName");

        return $response;
    }
}

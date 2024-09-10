<?php

namespace App\Controller\UI;

use App\Repository\DatasetRepository;
use App\Util\FundingOrgFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface as SerializerInterface;

/**
 * Dataset export report generator.
 */
class DatasetExportController extends AbstractController
{
    /**
     * This is a parameterless report, so all is in the default action.
     */
    #[Route('/dataset/export')]
    public function generateCsv(DatasetRepository $datasetRepository, SerializerInterface $serializer, FundingOrgFilter $fundingOrgFilter): Response
    {
        $fileName = 'GRP-Metadata-' . (new \DateTime('now'))->format('Ymd\THis') . '.csv';

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

<?php

namespace App\Controller\UI;

use App\Entity\Dataset;
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
     * This is a parameterless report, so all is in the default action.
     *
     * @return Response A Response instance.
     */
    #[Route('/grp/export')]
    public function defaultAction(EntityManagerInterface $entityManager, SerializerInterface $serializer, FundingOrgFilter $fundingOrgFilter)
    {
        $fileName = 'GRP-Metadata-' . (new DateTime('now'))->format('Ymd\THis') . '.csv';

        $researchGroupIDs = [];
        if ($fundingOrgFilter->isActive()) {
            $researchGroupIDs = $fundingOrgFilter->getResearchGroupsIdArray();
        }

        $datasetRepository = $entityManager->getRepository(Dataset::class);
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

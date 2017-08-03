<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DIF;

/**
 * The GOMRI datasets report generator.
 *
 * @Route("/gomri")
 */
class GomriReportController extends UIController
{
    /**
     * This is a parameterless report, so all is in the default action.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {
        $container = $this->container;
        $response = new StreamedResponse(function() use($container) {
            // final results array.
            $stats =  array();

            $entityManager = $container->get('doctrine')->getManager();

            // Query Identified.
            $queryString = "SELECT dif.creationTimeStamp " .
                "FROM " . Dataset::class . ' dataset ' .
                "JOIN dataset.dif dif " .
                "JOIN dataset.researchGroup researchgroup " .
                "WHERE researchgroup.fundingCycle < 700 " .
                "AND dif.status = 2" ;
            $query = $entityManager->createQuery($queryString);
            $stats["identified"] = $query->getResult();

            // Query Registered.
            $queryString = "SELECT datasetsubmission.creationTimeStamp " .
                "FROM " . DatasetSubmission::class . ' datasetsubmission ' .
                "JOIN datasetsubmission.dataset dataset " .
                "JOIN dataset.researchGroup researchgroup " .
                "WHERE datasetsubmission IN " .
                "   (SELECT MIN(subdatasetsubmission.id)" .
                "   FROM " . DatasetSubmission::class . " subdatasetsubmission" .
                "   WHERE subdatasetsubmission.datasetFileUri IS NOT null " .
                "   GROUP BY subdatasetsubmission.dataset)" .
                "AND researchgroup.fundingCycle < 700 ";
            $query = $entityManager->createQuery($queryString);
            $stats["registered"] = $query->getResult();

            // Query Available.
            $queryString = "SELECT datasetsubmission.creationTimeStamp " .
                "FROM " . DatasetSubmission::class . ' datasetsubmission ' .
                "JOIN datasetsubmission.dataset dataset " .
                "JOIN dataset.researchGroup researchgroup " .
                "WHERE datasetsubmission IN " .
                "(SELECT MIN(subdatasetsubmission.id) " .
                "   FROM " . DatasetSubmission::class . " subdatasetsubmission" .
                "   WHERE subdatasetsubmission.datasetFileUri is not null ".
                "   AND subdatasetsubmission.metadataStatus = 'Accepted' ".
                "   AND subdatasetsubmission.restrictions = 'None' ".
                "   AND (subdatasetsubmission.datasetFileTransferStatus = 'Completed' " .
                "       OR subdatasetsubmission.datasetFileTransferStatus = 'RemotelyHosted') " .
                "   GROUP BY subdatasetsubmission.dataset)" .
                "AND researchgroup.fundingCycle < 700 ";
            $query = $entityManager->createQuery($queryString);
            $stats["available"] = $query->getResult();

            var_dump($stats);

            $handle = fopen('php://output', 'r+');

            // Add header to CSV.
            fputcsv($handle, array('id', 'udi', 'status'));

            foreach($results as $result) {
                fputcsv($handle, $result);
            }

            fclose($handle);
        });

        //$response->headers->set('Content-Type', 'application/force-download');
        //$response->headers->set('Content-Disposition','attachment; filename="export.csv"');
        return $response;
    }
}

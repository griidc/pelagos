<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DIF;

use \DateTime;
use \DateInterval;

const MONTH_DAY_FORMAT = 'M Y';
const GOMRI_STRING = 'Gulf of Mexico Research Initiative (GoMRI)';

/**
 * The GOMRI datasets report generator.
 *
 * @Route("/gomri")
 */
class GomriReportController extends ReportController
{
    /**
     * This is a parameterless report, so all is in the default action.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction()
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }
          // Add header to CSV.
        return $this->writeCsvResponse(
            $this->getData(null)
        );
    }

    /**
     * This method gets data for the report.
     *
     * @return array  Return the data array
     */
    protected function getData()
    {
        //prepare labels
        $labels = array('labels' => array(
          'MONTH', 'YEAR',
          'MONTHLY IDENTIFIED',
          'TOTAL IDENTIFIED',
          'MONTHLY REGISTERED',
          'TOTAL REGISTERED',
          'MONTHLY AVAILABLE',
          'TOTAL AVAILABLE')
        );

        //prepare body's data
        $dataArray = array();
        $container = $this->container;
        $entityManager = $container->get('doctrine')->getManager();

        $dateTime = new DateTime('May 2012');

        $now = new DateTime('now');

        while ($dateTime < $now) {
            $dataArray[$dateTime->format(MONTH_DAY_FORMAT)] = array(
                'month' => $dateTime->format('m'),
                'year' => $dateTime->format('Y'),
                'monthly_identified' => 0,
                'total_identified' => 0,
                'monthly_registered' => 0,
                'total_registered' => 0,
                'monthly_available' => 0,
                'total_available' => 0
            );
            $dateTime->add(new DateInterval('P1M'));
        }

      // Query Identified.
        $queryString = 'SELECT dif.creationTimeStamp ' .
            'FROM ' . Dataset::class . ' dataset ' .
            'JOIN dataset.dif dif ' .
            'JOIN dataset.researchGroup researchgroup ' .
            'JOIN researchgroup.fundingCycle fundingCycle ' .
            'JOIN fundingCycle.fundingOrganization fundingOrganization ' .
            'WHERE fundingOrganization.name = :gomri ' .
            'AND dif.status = :difStatusApproved';
        $query = $entityManager->createQuery($queryString);
        $query->setParameters(array(
            'difStatusApproved' => DIF::STATUS_APPROVED,
            'gomri' => GOMRI_STRING,
        ));
        $results = $query->getResult();

        foreach ($results as $result) {
            $monthDay = date(MONTH_DAY_FORMAT, $result['creationTimeStamp']->getTimestamp());
            $dataArray[$monthDay]['monthly_identified']++;
        }

        // Query Registered.
        $queryString = 'SELECT datasetsubmission.creationTimeStamp ' .
            'FROM ' . DatasetSubmission::class . ' datasetsubmission ' .
            'JOIN datasetsubmission.dataset dataset ' .
            'JOIN dataset.researchGroup researchgroup ' .
            'JOIN researchgroup.fundingCycle fundingCycle ' .
            'JOIN fundingCycle.fundingOrganization fundingOrganization ' .
            'WHERE datasetsubmission IN ' .
            '   (SELECT MIN(subdatasetsubmission.id)' .
            '   FROM ' . DatasetSubmission::class . ' subdatasetsubmission' .
            '   WHERE subdatasetsubmission.datasetFileUri IS NOT null ' .
            '   GROUP BY subdatasetsubmission.dataset)' .
            'AND fundingOrganization.name = :gomri ';
        $query = $entityManager->createQuery($queryString);
        $query->setParameters(array('gomri' => GOMRI_STRING,));
        $results = $query->getResult();

        foreach ($results as $result) {
            $monthDay = date(MONTH_DAY_FORMAT, $result['creationTimeStamp']->getTimestamp());
            $dataArray[$monthDay]['monthly_registered']++;
        }

        // Query Available.
        $queryString = 'SELECT datasetsubmission.creationTimeStamp ' .
            'FROM ' . DatasetSubmission::class . ' datasetsubmission ' .
            'JOIN datasetsubmission.dataset dataset ' .
            'JOIN dataset.researchGroup researchgroup ' .
            'JOIN researchgroup.fundingCycle fundingCycle ' .
            'JOIN fundingCycle.fundingOrganization fundingOrganization ' .
            'WHERE datasetsubmission IN ' .
            '(SELECT MIN(subdatasetsubmission.id) ' .
            '   FROM ' . DatasetSubmission::class . ' subdatasetsubmission' .
            '   WHERE subdatasetsubmission.datasetFileUri is not null ' .
            '   AND subdatasetsubmission.metadataStatus = :metadataStatus ' .
            '   AND subdatasetsubmission.restrictions = :restrictions ' .
            '   AND (subdatasetsubmission.datasetFileTransferStatus = :fileTransferStatusCompleted ' .
            '       OR subdatasetsubmission.datasetFileTransferStatus = :fileTransferStatusRemotelyHosted) ' .
            '   GROUP BY subdatasetsubmission.dataset)' .
            'AND fundingOrganization.name = :gomri';
        $query = $entityManager->createQuery($queryString);
        $query->setParameters(array(
            'metadataStatus' => DatasetSubmission::METADATA_STATUS_ACCEPTED,
            'restrictions' => DatasetSubmission::RESTRICTION_NONE,
            'fileTransferStatusCompleted' => DatasetSubmission::TRANSFER_STATUS_COMPLETED,
            'fileTransferStatusRemotelyHosted' => DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED,
            'gomri' => GOMRI_STRING,
        ));
        $results = $query->getResult();

        foreach ($results as $result) {
            $monthDay = date(MONTH_DAY_FORMAT, $result['creationTimeStamp']->getTimestamp());
            $dataArray[$monthDay]['monthly_available']++;
        }

        $totalIdentified = 0;
        $totalRegistered = 0;
        $totalAvailable = 0;
        foreach ($dataArray as $monthDay => $stat) {
            $totalIdentified += $stat['monthly_identified'];
            $totalRegistered += $stat['monthly_registered'];
            $totalAvailable += $stat['monthly_available'];
            $dataArray[$monthDay]['total_identified'] = $totalIdentified;
            $dataArray[$monthDay]['total_registered'] = $totalRegistered;
            $dataArray[$monthDay]['total_available'] = $totalAvailable;
        }
        return array_merge($labels, $dataArray);
    }
}

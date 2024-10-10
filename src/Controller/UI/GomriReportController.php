<?php

namespace App\Controller\UI;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use DateTime;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;

const MONTH_DAY_FORMAT = 'M Y';
const GOMRI_STRING = 'Gulf of Mexico Research Initiative (GoMRI)';

/**
 * The GOMRI datasets report generator.
 */
class GomriReportController extends ReportController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * This is a parameterless report, so all is in the default action.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/gomri/v1', name: 'pelagos_app_ui_gomrireport_default')]
    public function defaultAction()
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }
          // Add header to CSV.
        return $this->writeCsvResponse(
            $this->getData('v1')
        );
    }

    /**
     * This is the version 2 Gomri Report.
     *
     *
     * @return Response A Response instance
     */
    #[Route(path: '/gomri/v2', name: 'pelagos_app_ui_gomrireport_versiontworeport')]
    public function versionTwoReportAction()
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }
        $customFileName = 'GomriReport-v2-' .
            (new DateTime('now'))->format(self::FILENAME_DATETIMEFORMAT) .
            '.csv';
        // Add header to CSV.
        return $this->writeCsvResponse(
            $this->getData(),
            $customFileName
        );
    }

    /**
     * This method gets data for the report.
     *
     * @param string $version The version no. of the report.
     *
     * @return array  Return the data array
     */
    protected function getData(string $version = 'v2')
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

        if ($version === 'v1') {
            $dataArray = $this->getVersionOneQueryData($dataArray);
        } else {
            $dataArray = $this->getVersionTwoQueryData($dataArray);
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

    /**
     * Query for version one report.
     *
     * @param array $dataArray The data array.
     *
     * @return array
     */
    private function getVersionOneQueryData(array $dataArray)
    {
        // Query Identified.
        $queryString = 'SELECT dif.creationTimeStamp ' .
            'FROM ' . Dataset::class . ' dataset ' .
            'JOIN dataset.dif dif ' .
            'JOIN dataset.researchGroup researchgroup ' .
            'JOIN researchgroup.fundingCycle fundingCycle ' .
            'JOIN fundingCycle.fundingOrganization fundingOrganization ' .
            'WHERE fundingOrganization.name = :gomri ' .
            'AND dif.status = :difStatusApproved';
        $query = $this->entityManager->createQuery($queryString);
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
        $query = $this->entityManager->createQuery($queryString);
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
            '   AND subdatasetsubmission.datasetStatus = :datasetStatus ' .
            '   AND subdatasetsubmission.restrictions = :restrictions ' .
            '   AND subdatasetsubmission.datasetFileTransferStatus = :fileTransferStatusCompleted ' .
            '   GROUP BY subdatasetsubmission.dataset)' .
            'AND fundingOrganization.name = :gomri';
        $query = $this->entityManager->createQuery($queryString);
        $query->setParameters(array(
            'datasetStatus' => Dataset::DATASET_STATUS_ACCEPTED,
            'restrictions' => DatasetSubmission::RESTRICTION_NONE,
            'fileTransferStatusCompleted' => DatasetSubmission::TRANSFER_STATUS_COMPLETED,
            'gomri' => GOMRI_STRING,
        ));
        $results = $query->getResult();

        foreach ($results as $result) {
            $monthDay = date(MONTH_DAY_FORMAT, $result['creationTimeStamp']->getTimestamp());
            $dataArray[$monthDay]['monthly_available']++;
        }

        return $dataArray;
    }

    /**
     * Query for version one report.
     *
     * @param array $dataArray The data array.
     *
     * @return array
     */
    private function getVersionTwoQueryData(array $dataArray)
    {
        // Query Identified (i.e. Datasets which have DIF approved).
        $qb = $this->entityManager->createQueryBuilder();
        $query = $qb
            ->select('dif.approvedDate')
            ->from('\App\Entity\Dataset', 'd')
            ->JOIN('\App\Entity\Dif', 'dif', 'WITH', 'd.dif = dif.id')
            ->JOIN('\App\Entity\ResearchGroup', 'rg', 'WITH', 'd.researchGroup = rg.id')
            ->JOIN('\App\Entity\FundingCycle', 'fc', 'WITH', 'rg.fundingCycle = fc.id')
            ->JOIN('\App\Entity\FundingOrganization', 'fo', 'WITH', 'fc.fundingOrganization = fo.id')
            ->where('dif.status = ?1')
            ->andWhere('fo.name = ?2')
            ->setParameter(1, DIF::STATUS_APPROVED)
            ->setParameter(2, GOMRI_STRING)
            ->getQuery();
        $results = $query->getResult();

        foreach ($results as $result) {
            $monthDay = date(MONTH_DAY_FORMAT, $result['approvedDate']->getTimestamp());
            $dataArray[$monthDay]['monthly_identified']++;
        }

        // Query Registered (i.e. Datasets which are submitted).
        $qb = $this->entityManager->createQueryBuilder();
        $query = $qb
            ->select('ds.submissionTimeStamp')
            ->from('\App\Entity\Dataset', 'd')
            ->JOIN('\App\Entity\DatasetSubmission', 'ds', 'WITH', 'd.datasetSubmission = ds.id')
            ->JOIN('\App\Entity\ResearchGroup', 'rg', 'WITH', 'd.researchGroup = rg.id')
            ->JOIN('\App\Entity\FundingCycle', 'fc', 'WITH', 'rg.fundingCycle = fc.id')
            ->JOIN('\App\Entity\FundingOrganization', 'fo', 'WITH', 'fc.fundingOrganization = fo.id')
            ->where('ds.datasetFileUri IS NOT null')
            ->andWhere('fo.name = ?1')
            ->setParameter(1, GOMRI_STRING)
            ->getQuery();
        $results = $query->getResult();

        foreach ($results as $result) {
            $monthDay = date(MONTH_DAY_FORMAT, $result['submissionTimeStamp']->getTimestamp());
            $dataArray[$monthDay]['monthly_registered']++;
        }

        // Query Available (i.e. Datasets which are publicly available).
        $qb = $this->entityManager->createQueryBuilder();

        $qb2 = $this->entityManager->createQueryBuilder()
            ->select('IDENTITY(ds2.dataset)')
            ->from('\App\Entity\DatasetSubmission', 'ds2')
            ->join('\App\Entity\Dataset', 'd2', 'WITH', 'ds2.dataset = d2.id')
            ->where('ds2.id = d2.datasetSubmission')
            ->andWhere('ds2.datasetFileUri is not null ')
            ->andWhere('ds2.restrictions = ?3')
            ->andWhere('ds2.datasetFileTransferStatus = ?4')
            ->getQuery();

        $query = $qb
            ->select('d.acceptedDate')
            ->from('\App\Entity\Dataset', 'd')
            ->JOIN('\App\Entity\ResearchGroup', 'rg', 'WITH', 'd.researchGroup = rg.id')
            ->JOIN('\App\Entity\FundingCycle', 'fc', 'WITH', 'rg.fundingCycle = fc.id')
            ->JOIN('\App\Entity\FundingOrganization', 'fo', 'WITH', 'fc.fundingOrganization = fo.id')
            ->where(
                $qb->expr()->in('d.id', $qb2->getDQL())
            )
            ->andWhere('fo.name = ?1')
            ->andWhere('d.datasetStatus = ?2')
            ->setParameter(1, GOMRI_STRING)
            ->setParameter(2, Dataset::DATASET_STATUS_ACCEPTED)
            ->setParameter(3, DatasetSubmission::RESTRICTION_NONE)
            ->setParameter(4, DatasetSubmission::TRANSFER_STATUS_COMPLETED)
            ->getQuery();

        $results = $query->getResult();

        foreach ($results as $result) {
            $monthDay = date(MONTH_DAY_FORMAT, $result['acceptedDate']->getTimestamp());
            $dataArray[$monthDay]['monthly_available']++;
        }

        return $dataArray;
    }
}

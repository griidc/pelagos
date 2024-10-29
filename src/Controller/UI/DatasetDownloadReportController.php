<?php

namespace App\Controller\UI;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;
use App\Form\ReportDatasetDownloadType;
use App\Exception\InvalidDateSelectedException;
use App\Entity\Dataset;
use App\Entity\LogActionItem;
use App\Entity\Person;
use App\Entity\DatasetSubmission;
use App\Entity\Account;
use App\Entity\PersonDataRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * The dataset download report generator.
 */
class DatasetDownloadReportController extends ReportController
{
    //DateTime format used for date range
    const INREPORT_DATETIMEFORMAT = 'm-d-Y';

    const TIMESTAMP_REPORT = 'timeStampReport';

    const UDI_REPORT = 'udiReport';

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * This defaultAction generates the form to select the date range for the report.
     *
     * @param Request $request Message response.
     *
     *
     * @throws InvalidDateSelectedException Selected Dates are invalid.
     * @return Response|StreamedResponse A Response instance.
     */
    #[Route(path: '/dataset-download-report', name: 'pelagos_app_ui_datasetdownloadreport_default')]
    public function defaultAction(Request $request, FormFactoryInterface $formFactory)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }
        $form = $formFactory->createNamed('', ReportDatasetDownloadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $startDate = $form->getData()['startDate'];
                $endDate = $form->getData()['endDate'];
                if ($startDate && $endDate) {
                    if ($startDate <= $endDate) {
                        $data = $this->getData([
                            'startDate' => $startDate,
                            'endDate' => $endDate
                        ]);
                        return $this->writeCsvResponse(
                            $data,
                            'DatasetDownloadReport-' .
                            (new \DateTime('now'))->format(parent::FILENAME_DATETIMEFORMAT) .
                            '.csv'
                        );
                    }
                } else {
                    throw new InvalidDateSelectedException('The dates selected are invalid.');
                }
            }
        }
        return $this->render(
            'Reports/ReportDatasetDownload.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * This method gets data for the report.
     *
     * @param array|NULL $options Additional parameters needed to run the query.
     *
     * @return array  Return the data array
     */
    protected function getData(array $options = null)
    {
        //prepare extra headers
        $additionalHeaders = array('additionalHeaders' =>
            array('START DATE', $options['startDate']->format(self::INREPORT_DATETIMEFORMAT)),
            array('END DATE', $options['endDate']->format(self::INREPORT_DATETIMEFORMAT)),
            array());

        //prepare labels
        $labels = $this->getLabels(self::UDI_REPORT);

        //prepare body's data
        $dataArray = array();

        //Query
        $query = $this->getQuery(self::UDI_REPORT, $options);

        $results = $query->getResult();

        //process result query into an array with organized data
        $currentIndex = 0;
        foreach ($results as $result) {
            //initialize array with key  = udi, set title and primary POC
            if (isset($dataArray[$currentIndex]['udi']) && $result['udi'] != $dataArray[$currentIndex]['udi']) {
                $currentIndex++;
            }
            if (!isset($dataArray[$currentIndex])) {
                $dataArray[$currentIndex] = array(
                    'udi' => $result['udi'],
                    'title' => null,
                    'primaryPointOfContact' => null,
                    'primaryPointOfContactEmail' => null,
                    'totalCount' => 0,
                    'GoMRI' => 0,
                    'NonGoMRI' => 0,
                    'fileSize' => null
                );

                $dataset = $this->entityManager->getRepository(Dataset::class)
                    ->findOneBy(array('udi' => $result['udi']));

                $dataArray[$currentIndex]['title'] = $dataset->getTitle();

                $primaryPointOfContact = $dataset->getPrimaryPointOfContact();

                if ($primaryPointOfContact instanceof Person) {
                    $dataArray[$currentIndex]['primaryPointOfContact']
                        = $primaryPointOfContact->getLastName() .
                        ', ' . $primaryPointOfContact
                        ->getFirstName();
                    $dataArray[$currentIndex]['primaryPointOfContactEmail']
                        = $primaryPointOfContact
                        ->getEmailAddress();
                }

                // get file size from dataset submission
                $dataArray[$currentIndex]['fileSize'] = $this->getFileSize($dataset);
            }
            //count user downloads and total download
            if ($result['payLoad']['userType'] == 'GoMRI') {
                $dataArray[$currentIndex]['GoMRI']++;
            } else {
                $dataArray[$currentIndex]['NonGoMRI']++;
            }
            $dataArray[$currentIndex]['totalCount']++;
        }
        return array_merge($this->getDefaultHeaders(), $additionalHeaders, $labels, $dataArray);
    }

    /**
     * Used to format the file size units to MB.
     *
     * @param integer|null $fileSizeBytes File size in bytes.
     *
     * @return float
     */
    private function formatSizeUnits(?int $fileSizeBytes)
    {
        if ($fileSizeBytes) {
            // Formats the size to MB
            $fileSizeBytes = number_format(($fileSizeBytes / 1000000), 6);
        }

        return $fileSizeBytes;
    }

    /**
     * Generates report of dataset downloads based on timestamp.
     *
     *
     * @return Response|StreamedResponse A Response instance.
     */
    #[Route(path: '/dataset-download-report/timestamp', name: 'pelagos_app_ui_datasetdownloadreport_timestampreport')]
    public function timeStampReportAction()
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $data = $this->getDataTimeStamp();

        return $this->writeCsvResponse(
            $data,
            'DatasetDownloadTimeStampReport-' .
            (new \DateTime('now'))->format(parent::FILENAME_DATETIMEFORMAT) .
            '.csv'
        );
    }

    /**
     * Get data for the time stamp report.
     *
     * @return array  Return the data array
     */
    protected function getDataTimeStamp()
    {
        //prepare labels
        $labels = $this->getLabels(self::TIMESTAMP_REPORT);

        //prepare body's data
        $dataArray = array();

        $query = $this->getQuery(self::TIMESTAMP_REPORT);

        $results = $query->getResult();

        $griidcArray = $this->excludeGriidcStaff();

        //process result query into an array with organized data
        $currentIndex = 0;
        foreach ($results as $result) {
            //skip the row if the search is done by a Griidc Staff
            if (
                isset($result['payLoad']['userId']) &&
                in_array($result['payLoad']['userId'], $griidcArray)
            ) {
                continue;
            }
            //initialize array with key  = dateTimeStamp, set title and primary POC
            if (
                isset($dataArray[$currentIndex]['dateTimeStamp'])
                and $result['creationTimeStamp'] !== $dataArray[$currentIndex]['dateTimeStamp']
            ) {
                $currentIndex++;
            }
            if (!isset($dataArray[$currentIndex])) {
                $dataArray[$currentIndex] = array(
                    'dateTimeStamp' => $result['creationTimeStamp']->format(parent::INREPORT_TIMESTAMPFORMAT),
                    'udi' => $result['udi'],
                    'title' => null,
                    'fileSize' => null
                );

                $dataset = $this->entityManager->getRepository(Dataset::class)
                    ->findOneBy(array('udi' => $result['udi']));

                $dataArray[$currentIndex]['title'] = $dataset->getTitle();


                // get file size from dataset submission
                $dataArray[$currentIndex]['fileSize'] = $this->getFileSize($dataset);
            }
        }

        return array_merge($this->getDefaultHeaders(), $labels, $dataArray);
    }

    /**
     * Get labels for the report.
     *
     * @param string $reportName Name of the report.
     *
     * @return array
     */
    private function getLabels(string $reportName): array
    {
        //prepare labels
        $labels = array();
        if ($reportName === self::TIMESTAMP_REPORT) {
            $labels = array('labels' => array(
                'DateTime Stamp',
                'UDI',
                'TITLE',
                'FILE SIZE(MB)'
            ));
        } else {
            $labels = array('labels' => array(
                'UDI',
                'TITLE',
                'PRIMARY POINT OF CONTACT',
                'PRIMARY POINT OF CONTACT EMAIL',
                'TOTAL DOWNLOADS',
                '# OF GOMRI DOWNLOADS',
                '# OF NON-GOMRI DOWNLOADS',
                'FILE SIZE(MB)'
            ));
        }

        return $labels;
    }

    /**
     * Get the correct query for the report.
     *
     * @param string     $reportName Name of the report.
     * @param array|null $options    Additional parameters needed to run the query.
     *
     * @return Query
     */
    private function getQuery(string $reportName, array $options = null): Query
    {
        //Query
        if ($reportName === self::TIMESTAMP_REPORT) {
            $qb = $this->entityManager->createQueryBuilder();
            $query = $qb
                ->select('log.creationTimeStamp, d.udi, log.payLoad')
                ->from('\App\Entity\LogActionItem', 'log')
                ->join('\App\Entity\Dataset', 'd', Query\Expr\Join::WITH, 'log.subjectEntityId = d.id')
                ->where('log.subjectEntityName = ?1')
                ->andWhere('log.actionName = ?2')
                ->orderBy('log.creationTimeStamp', 'ASC')
                ->setParameter(1, 'Pelagos\Entity\Dataset')
                ->setParameter(2, 'File Download')
                ->getQuery();
        } else {
            $queryString = 'SELECT dataset.udi,log.payLoad from ' .
                LogActionItem::class . ' log join ' . Dataset::class . ' dataset with
                log.subjectEntityId = dataset.id where log.actionName = :actionName and
                log.subjectEntityName = :subjectEntityName and
                log.creationTimeStamp >= :startDate
                and log.creationTimeStamp <= :endDate order by dataset.udi ASC';

            $query = $this->entityManager->createQuery($queryString);
            $endDateInclusively = clone $options['endDate'];
            $endDateInclusively = $endDateInclusively->add(new \DateInterval('P1D'));
            $query->setParameters([
                'actionName' => 'File Download',
                'subjectEntityName' => 'Pelagos\Entity\Dataset',
                'startDate' => $options['startDate'],
                'endDate' => $endDateInclusively
            ]);
        }

        return $query;
    }

    /**
     * Get the file size for the dataset.
     *
     * @param Dataset $dataset The dataset instance.
     *
     * @return float
     */
    private function getFileSize(Dataset $dataset): float
    {
        $fileSize = null;
        $datasetSubmission = $dataset->getDatasetSubmission();
        if ($datasetSubmission instanceof DatasetSubmission) {
            $fileSize = (float) $this->formatSizeUnits($datasetSubmission->getDatasetFileSize());
        }

        return $fileSize;
    }

    /**
     * Get the griidc staff user info.
     *
     * @return array
     */
    private function excludeGriidcStaff(): array
    {
        //get user Ids of Griidc Staff to exclude from the report with personDataRepository roles of:
        //Manager (1), Developer (2), Support (3), Subject Matter Expert (4)
        $griidcUserQueryString = 'SELECT account.userId FROM ' . PersonDataRepository::class .
            ' personDataRepository JOIN ' . Person::class .
            ' person WITH person.id = personDataRepository.person JOIN ' . Account::class .
            ' account WITH account.person = person.id WHERE personDataRepository.role in (1, 2, 3, 4) ';
        $griidcUserResult = $this->entityManager->createQuery($griidcUserQueryString)->getScalarResult();
        $griidcArray = array_column($griidcUserResult, 'userId');

        return $griidcArray;
    }
}

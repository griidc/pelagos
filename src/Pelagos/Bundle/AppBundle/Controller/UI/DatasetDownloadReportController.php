<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Bundle\AppBundle\Form\ReportDatasetDownloadType;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\LogActionItem;
use Pelagos\Entity\Metadata;
use Pelagos\Exception\InvalidDateSelectedException;

use Pelagos\Util\ISOMetadataExtractorUtil;

/**
 * The dataset download report generator.
 *
 * @Route("/dataset-download-report")
 */
class DatasetDownloadReportController extends ReportController
{
    //DateTime format used for date range
    const INREPORT_DATETIMEFORMAT = 'm-d-Y';

    /**
     * This defaultAction generates the form to select the date range for the report.
     *
     * @param Request $request Message response.
     *
     * @Route("")
     *
     * @throws InvalidDateSelectedException Selected Dates are invalid.
     *
     * @return Response|StreamedResponse A Response instance.
     */
    public function defaultAction(Request $request)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }
        $form = $this->get('form.factory')->createNamed(
            null,
            ReportDatasetDownloadType::class,
            null
        );
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
            'PelagosAppBundle:template:ReportDatasetDownload.html.twig',
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
        $labels = array('labels' => array(
            'UDI',
            'TITLE',
            'PRIMARY POINT OF CONTACT',
            'PRIMARY POINT OF CONTACT EMAIL',
            'TOTAL DOWNLOADS',
            '# OF GOMRI DOWNLOADS',
            '# OF GOOGLE DOWNLOADS'
        ));

      //prepare body's data
        $dataArray = array();
        $container = $this->container;
        $entityManager = $container->get('doctrine')->getManager();
        //Query
        $queryString = 'SELECT dataset.udi,log.payLoad from ' .
            LogActionItem::class . ' log join ' . Dataset::class . ' dataset with
                log.subjectEntityId = dataset.id where log.actionName = :actionName and
                log.subjectEntityName = :subjectEntityName and 
                log.creationTimeStamp >= :startDate 
                and log.creationTimeStamp <= :endDate order by dataset.udi ASC';

        $query = $entityManager->createQuery($queryString);
        $endDateInclusively = clone $options['endDate'];
        $endDateInclusively = $endDateInclusively->add(new \DateInterval('P1D'));
        $query->setParameters([
            'actionName' => 'File Download',
            'subjectEntityName' => 'Pelagos\Entity\Dataset',
            'startDate' => $options['startDate'],
            'endDate' => $endDateInclusively
        ]);
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
                );

                $dataset = $this->container->get('doctrine')->getRepository(Dataset::class)
                    ->findOneBy(array('udi' => $result['udi']));

                $dataArray[$currentIndex]['title'] = $dataset->getTitle();
                //get Primary point of contact from the XML
                $datasetSubmission = $dataset->getDatasetSubmission();
                if ($datasetSubmission instanceof DatasetSubmission
                    and $dataset->getMetadata() instanceof Metadata) {
                    $datasetSubmission->getDatasetContacts()->clear();
                    ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues(
                        $dataset->getMetadata()->getXml(),
                        $datasetSubmission,
                        $this->getDoctrine()->getManager()
                    );
                    $dataset->setDatasetSubmission($datasetSubmission);
                    $dataArray[$currentIndex]['primaryPointOfContact']
                        = $dataset->getPrimaryPointOfContact()->getLastName() .
                        ', ' . $dataset->getPrimaryPointOfContact()->getFirstName();
                    $dataArray[$currentIndex]['primaryPointOfContactEmail']
                        = $dataset->getPrimaryPointOfContact()->getEmailAddress();
                }
            }
            //count user downloads and total download
            if ($result['payLoad']['userType'] == 'GoMRI') {
                $dataArray[$currentIndex]['GoMRI']++;
            } else {
                if ($result['payLoad']['userType'] == 'Non-GoMRI') {
                    $dataArray[$currentIndex]['NonGoMRI']++;
                }
            }
            $dataArray[$currentIndex]['totalCount']++;
        }
        return array_merge($this->getDefaultHeaders(), $additionalHeaders, $labels, $dataArray);
    }
}

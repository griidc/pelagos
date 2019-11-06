<?php

namespace App\Controller\UI;

use DateTime;
use App\Entity\Account;
use App\Entity\LogActionItem;
use App\Entity\Person;
use App\Entity\PersonDataRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * The GOMRI datasets report generator.
 */
class SearchTermsReportController extends ReportController
{
    //timestamp used in filename
    const FILENAME_TIMESTAMPFORMAT = 'Y-m-d_Hi';

  /**
   * This is a parameterless report, so all is in the default action.
   *
   * @Route("/search-terms-report", name="pelagos_app_ui_searchtermsreport_default")
   *
   * @return Response A Response instance.
   */
    public function defaultAction()
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        // Add header to CSV.
        return $this->writeCsvResponse(
            $this->getData(),
            'SearchTermsReport-' . (
                new DateTime('now', new \DateTimeZone('UTC'))
            )
                ->format(self::FILENAME_TIMESTAMPFORMAT) . '.csv'
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
            'SESSION ID', 'TIMESTAMP', 'SEARCH TERMS', 'GEOFILTER USED', 'NUMBER OF RESULTS',
            '1ST SCORE', '2ND SCORE',
            '1ST UDI', '1ST TITLE', '1ST LINK',
            '2ND UDI', '2ND TITLE', '2ND LINK',
            'GEOFILTER WKT'
            )
        );

        //prepare body's data
        $dataArray = array();
        $entityManager = $this->getDoctrine()->getManager();
        //Query
        $queryString = 'SELECT log.creationTimeStamp, log.payLoad from ' .
          LogActionItem::class . ' log where log.actionName = :actionName order by log.creationTimeStamp DESC';
        $query = $entityManager->createQuery($queryString);
        $query->setParameters(['actionName' => 'Search']);
        $results = $query->getResult();

        //get user Ids of Griidc Staff to exclude from the report with personDataRepository roles of:
        //Manager (1), Developer (2), Support (3), Subject Matter Expert (4)
        $griidcUserQueryString = 'SELECT account.userId FROM ' . PersonDataRepository::class .
            ' personDataRepository JOIN ' . Person::class .
            ' person WITH person.id = personDataRepository.person JOIN ' . Account::class .
            ' account WITH account.person = person.id WHERE personDataRepository.role in (1, 2, 3, 4) ';
        $griidcUserResult = $entityManager->createQuery($griidcUserQueryString)->getScalarResult();
        $griidcArray = array_column($griidcUserResult, 'userId');

        //process result query into an array with organized data
        foreach ($results as $result) {
            //skip the row if the search is done by a Griidc Staff
            if (isset($result['payLoad']['clientInfo']['userId']) &&
                in_array($result['payLoad']['clientInfo']['userId'], $griidcArray)) {
                continue;
            }

            $searchResults = array
            (
                '1stScore' => '',
                '2ndScore' => '',
                '1stUDI' => '',
                '1stTitle' => '',
                '1stLink' => '',
                '2ndUDI' => '',
                '2ndTitle' => '',
                '2ndLink' => ''
            );

            $numResults = $result['payLoad']['numResults'];
            if ($numResults > 0) {
                $searchResults['1stScore'] = $result['payLoad']['results'][0]['score'];
                $searchResults['1stUDI'] = $result['payLoad']['results'][0]['udi'];
                $searchResults['1stTitle'] = $result['payLoad']['results'][0]['title'];
                $searchResults['1stLink'] = $this->container->get('router')
                    ->generate(
                        'pelagos_app_ui_dataland_default',
                        array('udi' => $searchResults['1stUDI']),
                        UrlGenerator::ABSOLUTE_URL
                    );
            }
            if ($numResults > 2) {
                $searchResults['2ndScore'] = $result['payLoad']['results'][1]['score'];
                $searchResults['2ndUDI'] = $result['payLoad']['results'][1]['udi'];
                $searchResults['2ndTitle'] = $result['payLoad']['results'][1]['title'];
                $searchResults['2ndLink'] = $this->container->get('router')
                    ->generate(
                        'pelagos_app_ui_dataland_default',
                        array('udi' => $searchResults['2ndUDI']),
                        UrlGenerator::ABSOLUTE_URL
                    );
            }

            $dataArray[] = array_merge(
                array
                (
                    'sessionID' => $result['payLoad']['clientInfo']['sessionId'],
                    'timeStamp' => $result['creationTimeStamp']->format(parent::INREPORT_TIMESTAMPFORMAT),
                    'searchTerms' => $result['payLoad']['filters']['textFilter'],
                    'geofilterUsed' => $result['payLoad']['filters']['geoFilter'] !== null ? 1 : 0,
                    'numResults' => $numResults
                ),
                $searchResults,
                array
                (
                    'geofilterWkt' => $result['payLoad']['filters']['geoFilter'] !== null ? $result['payLoad']['filters']['geoFilter'] : ''
                )
            );
        }
        return array_merge($labels, $dataArray);
    }
}

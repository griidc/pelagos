<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Elastica\ResultSet;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DIF;
use Pelagos\Entity\Metadata;

use Pelagos\Util\ISOMetadataExtractorUtil;

/**
 * The Data Discovery controller.
 *
 * @Route("/data-discovery")
 */
class DataDiscoveryController extends UIController
{
    /**
     * The default action.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("")
     * @Method("GET")
     *
     * @return Response
     */
    public function defaultAction(Request $request)
    {
        return $this->render(
            'PelagosAppBundle:DataDiscovery:index.html.twig',
            array(
                'defaultFilter' => $request->query->get('filter'),
                'pageName' => 'data-discovery',
                'download' => false,
            )
        );
    }

    /**
     * The datasets action.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/datasets")
     * @Method("GET")
     *
     * @return Response
     */
    public function datasetsAction(Request $request)
    {
        $criteria = array();
        if (!empty($request->query->get('by')) and !empty($request->query->get('id'))) {
            switch ($request->query->get('by')) {
                case 'fundSrc':
                    $criteria['researchGroup.fundingCycle.id'] = array(
                        $request->query->get('id')
                    );
                    break;
                case 'projectId':
                    $criteria['researchGroup.id'] = array(
                        $request->query->get('id')
                    );
                    break;
            }
        }
        $textFilter = null;
        if (!empty($request->query->get('filter'))) {
            $textFilter = $request->query->get('filter');
        }
        $geoFilter = null;
        if (!empty($request->query->get('geo_filter'))) {
            $geoFilter = $request->query->get('geo_filter');
        }
        $datasetIndex = $this->get('pelagos.util.dataset_index');

        //run a query without availability status & log search terms
        if ($textFilter != null || $geoFilter != null) {
            $searchTermsQueryResult = $datasetIndex->search(
                $criteria,
                $textFilter,
                $geoFilter
            );
            $this->dispatchSearchTermsLogEvent($request, $searchTermsQueryResult);
        }

        return $this->render(
            'PelagosAppBundle:DataDiscovery:datasets.html.twig',
            array(
                'datasets' => array(
                    'available' => $datasetIndex->search(
                        array_merge(
                            $criteria,
                            array(
                                'availabilityStatus' => array(
                                    DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                                    DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                                )
                            )
                        ),
                        $textFilter,
                        $geoFilter
                    ),
                    'restricted' => $datasetIndex->search(
                        array_merge(
                            $criteria,
                            array(
                                'availabilityStatus' => array(
                                    DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED,
                                    DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED,
                                )
                            )
                        ),
                        $textFilter,
                        $geoFilter
                    ),
                    'inReview' => $datasetIndex->search(
                        array_merge(
                            $criteria,
                            array(
                                'availabilityStatus' => array(
                                    DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL,
                                )
                            )
                        ),
                        $textFilter,
                        $geoFilter
                    ),
                    'identified' => $datasetIndex->search(
                        array_merge(
                            $criteria,
                            array(
                                'availabilityStatus' => array(
                                    DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE,
                                    DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION,
                                ),
                                'identifiedStatus' => array(
                                    DIF::STATUS_APPROVED,
                                ),
                            )
                        ),
                        $textFilter,
                        $geoFilter
                    ),
                ),
            )
        );
    }

    /**
     * Show details for a dataset.
     *
     * @param integer $id The id of the Dataset.
     *
     * @Route("/show-details/{id}")
     * @Method("GET")
     *
     * @return Response
     */
    public function showDetailsAction($id)
    {
        $dataset = $this->get('pelagos.entity.handler')->get(Dataset::class, $id);

        $datasetSubmission = $dataset->getDatasetSubmission();

        // If we have approved Metadata, load contact into datasetSubmission.
        if ($datasetSubmission instanceof DatasetSubmission
            and $dataset->getMetadata() instanceof Metadata
        ) {
            $datasetSubmission->getDatasetContacts()->clear();
            ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues(
                $dataset->getMetadata()->getXml(),
                $datasetSubmission,
                $this->getDoctrine()->getManager()
            );
        }

        return $this->render(
            'PelagosAppBundle:DataDiscovery:dataset_details.html.twig',
            array(
                'dataset' => $dataset
            )
        );
    }

    /**
     * This dispatches a search term log event.
     *
     * @param Request             $request      The request passed from datasetAction.
     * @param \Elastica\ResultSet $searchResult Results returned by a search.
     *
     * @return void
     */
    protected function dispatchSearchTermsLogEvent(Request $request, ResultSet $searchResult)
    {
        //get logged in user's info
        $clientInfo = array(
            'sessionId' => $request->getSession()->getId(),
            'clientIp' => $request->getClientIp(),
            'userAgent' => $request->headers->get('User-Agent')
        );
        $userType = get_class($this->getUser());
        switch ($userType) {
            case 'Pelagos\Entity\Account':
                $clientInfo['userType'] = 'GoMRI';
                $clientInfo['userId'] = $this->getUser()->getUserId();
                break;
            case 'HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser':
                $clientInfo['userType'] = 'Non-GoMRI';
                $clientInfo['userId'] = $this->getUser()->getUsername();
                break;
            default:
                break;
        }

        $numResults = $searchResult->count();
        //get the first 2 results (if available)
        $results = array();
        if ($numResults > 0) {
            $results[] = [
                'udi' => $searchResult[0]->getSource()['udi'],
                'title' => $searchResult[0]->getSource()['title'],
                'score' => $searchResult[0]->getScore()
            ];
        }
        if ($numResults > 1) {
            $results[] = [
                'udi' => $searchResult[1]->getSource()['udi'],
                'title' => $searchResult[1]->getSource()['title'],
                'score' => $searchResult[1]->getScore()
            ];
        }
      //get filters
        $filters = array(
            'textFilter' => !empty($request->query->get('filter')) ? $request->query->get('filter') : null ,
            'geoFilter' => !empty($request->query->get('geo_filter')) ? $request->query->get('geo_filter') : null,
            'by' => !empty($request->query->get('by')) ? $request->query->get('by') : null,
            'id' => !empty($request->query->get('id')) ? $request->query->get('id') : null);

        //dispatch the event
        $this->container->get('pelagos.event.log_action_item_event_dispatcher')->dispatch(
            array(
                'actionName' => 'Search',
                    'subjectEntityName' => null,
                    'subjectEntityId' => null,
                    'payLoad' => array(
                        'clientInfo' => $clientInfo,
                        'filters' => $filters,
                        'numResults' => $numResults,
                        'results' => $results
                    )
            ),
            'search_terms_log'
        );
    }
}

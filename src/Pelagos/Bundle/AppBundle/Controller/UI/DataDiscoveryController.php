<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Elastica\ResultSet;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DIF;

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
     * The dataset counts action.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/dataset-count")
     * @Method("GET")
     *
     * @return Response
     */
    public function countAction(Request $request)
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
        if (($textFilter != null && strlen(trim($textFilter)) > 0) || $geoFilter != null) {
            $searchTermsQueryResult = $datasetIndex->search(
                array_merge(
                    $criteria,
                    array('availabilityStatus' => array(
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                        DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED,
                        DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED,
                        DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL,
                        DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE,
                        DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION,
                    ),
                        'identifiedStatus' => array(
                            DIF::STATUS_APPROVED
                        )
                    )
                ),
                $textFilter,
                $geoFilter
            );
            $this->dispatchSearchTermsLogEvent($request, $searchTermsQueryResult);
        }
        
        return $this->render(
            'PelagosAppBundle:DataDiscovery:datasets.html.twig',
            array(
                'counts' => array(
                    'available' => $datasetIndex->count(
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
                    'restricted' => $datasetIndex->count(
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
                    'inReview' => $datasetIndex->count(
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
                    'identified' => $datasetIndex->count(
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
     * The datasets search action.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/dataset-results")
     * @Method("GET")
     *
     * @return JsonResponse
     */
    public function searchAction(Request $request)
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

        $currentIndex = $request->query->get('current_index');
        $bulkSize = $request->query->get('bulk_size');

        $datasetIndex = $this->get('pelagos.util.dataset_index');


        $activeTabIndex = $request->query->get('active_tab_index');
        switch ($activeTabIndex) {
            case 1:
                //restricted
                $availabilityStatus = array (
                    'availabilityStatus' => array(
                        DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED,
                        DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED,
                    )
                );
                break;
            case 2:
                // inReview
                $availabilityStatus = array(
                        'availabilityStatus' => array(
                            DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL,
                        )
                    );
                break;
            case 3:
                //identified
                $availabilityStatus = array(
                    'availabilityStatus' => array(
                        DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE,
                        DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION,
                    ),
                    'identifiedStatus' => array(
                        DIF::STATUS_APPROVED,
                    ),
                );
                break;
            default:
                //case 0 or default case (available)
                $availabilityStatus = array(
                    'availabilityStatus' => array(
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                    )
                );
        }

        $results = $datasetIndex->search(
            array_merge(
                $criteria,
                $availabilityStatus
            ),
            $textFilter,
            $geoFilter
        )->getResponse()->getData()['hits']['hits'];

        if ((count($results) - $currentIndex) >= $bulkSize) {
            $results = array_slice($results, $currentIndex, $bulkSize);
        }
        else {
            //return the last bulk of data (the last bulk is less than bulk size)
            $results = array_slice($results, $currentIndex, count($results) - $currentIndex);
        }

        return new JsonResponse(json_encode($results));
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
            'clientIp' => $request->getClientIp()
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
            $results[0] = [
                'udi' => $searchResult[0]->getSource()['udi'],
                'title' => $searchResult[0]->getSource()['title'],
                'score' => empty($searchResult[0]->getScore()) ? 0 : $searchResult[0]->getScore()
                 //search without a search term will return 0 score instead of an empty array
            ];
        }
        if ($numResults > 1) {
            $results[1] = [
                'udi' => $searchResult[1]->getSource()['udi'],
                'title' => $searchResult[1]->getSource()['title'],
                'score' => empty($searchResult[1]->getScore()) ? 0 : $searchResult[1]->getScore()
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

<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Show details for a dataset.
     *
     * @param integer $id The id of the Dataset.
     *
     * @Route("/sitemap.xml")
     * @Method("GET")
        *
     * @return Response
     */
    public function showSiteMapXml()
    {


        $container = $this->container;
        $response = new StreamedResponse(function () use ($container) {
            $datasets = $container->get('pelagos.entity.handler')->getAll(Dataset::class);
            echo $this->renderView(
                'PelagosAppBundle::sitemap.xml.twig',
                array(
                    'datasets' => $datasets
                )
            );
        });

        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}

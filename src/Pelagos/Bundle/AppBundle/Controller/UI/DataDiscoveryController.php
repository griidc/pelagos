<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Doctrine\ORM\Query;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

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
     * @Route("")
     * @Method("GET")
     *
     * @return Response
     */
    public function defaultAction()
    {
        return $this->render(
            'PelagosAppBundle:DataDiscovery:index.html.twig',
            array(
                'treePaneCollapsed' => false,
                'defaultFilter' => '',
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
        $filters = array();
        if (!empty($request->query->get('by')) and !empty($request->query->get('id'))) {
            switch ($request->query->get('by')) {
                case 'fundSrc':
                    $filters['fundingCycle'] = array(
                        $request->query->get('id')
                    );
                    break;
                case 'projectId':
                    $filters['researchGroup'] = array(
                        $request->query->get('id')
                    );
                    break;
            }
        }
        $geoFilter = null;
        if (!empty($request->query->get('geo_filter'))) {
            $geoFilter = $request->query->get('geo_filter');
        }
        $textFilter = null;
        if (!empty($request->query->get('filter'))) {
            $textFilter = $request->query->get('filter');
        }
        $datasets = array();
        $datasets['available'] = $this->getDatasets(
            array_merge(
                $filters,
                array(
                    'dataset.availabilityStatus' => array(
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                    )
                )
            ),
            $geoFilter,
            $textFilter
        );
        $datasets['restricted'] = $this->getDatasets(
            array_merge(
                $filters,
                array(
                    'dataset.availabilityStatus' => array(
                        DatasetSubmission::AVAILABILITY_STATUS_AVAILABLE_WITH_APPROVAL,
                        DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED,
                        DatasetSubmission::AVAILABILITY_STATUS_AVAILABLE_WITH_APPROVAL_REMOTELY_HOSTED,
                        DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED,
                    )
                )
            ),
            $geoFilter,
            $textFilter
        );
        $datasets['inReview'] = $this->getDatasets(
            array_merge(
                $filters,
                array(
                    'dataset.availabilityStatus' => array(
                        DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL,
                    )
                )
            ),
            $geoFilter,
            $textFilter
        );
        $datasets['identified'] = $this->getDatasets(
            array_merge(
                $filters,
                array(
                    'dataset.availabilityStatus' => array(
                        DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE,
                    )
                )
            ),
            $geoFilter,
            $textFilter
        );

        return $this->render(
            'PelagosAppBundle:DataDiscovery:datasets.html.twig',
            array(
                'datasets' => $datasets,
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
        return $this->render(
            'PelagosAppBundle:DataDiscovery:dataset_details.html.twig',
            array(
                'dataset' => $this->get('pelagos.entity.handler')->get(Dataset::class, $id)
            )
        );
    }

    /**
     * Get datasets with properties matching any values specified by $criteria.
     *
     * @param array  $criteria   An array of criteria.
     * @param string $geoFilter  A WKT string of a geometry to filter by.
     * @param string $textFilter A string of words to filter by.
     *
     * @return array
     */
    protected function getDatasets(array $criteria, $geoFilter = null, $textFilter = null)
    {
        $qb = $this->get('doctrine.orm.entity_manager')
                   ->getRepository(Dataset::class)
                   ->createQueryBuilder('dataset');
        $qb->select('dataset, dif, datasetSubmission, metadata, researchGroup');
        $qb->addSelect('ST_AsText(ST_GeomFromGML(dif.spatialExtentGeometry, 4326)) difSpatialExtentGeometry');
        $qb->join('dataset.dif', 'dif');
        $qb->leftJoin('dataset.datasetSubmission', 'datasetSubmission');
        $qb->leftJoin('dataset.metadata', 'metadata');
        $qb->join('dataset.researchGroup', 'researchGroup');
        $qb->join('researchGroup.fundingCycle', 'fundingCycle');
        foreach ($criteria as $property => $values) {
            $orX = null;
            foreach ($values as $value) {
                if (null === $orX) {
                    $orX = $qb->expr()->orX(
                        $qb->expr()->eq(
                            $property,
                            $qb->expr()->literal($value)
                        )
                    );
                } else {
                    $orX->add(
                        $qb->expr()->eq(
                            $property,
                            $qb->expr()->literal($value)
                        )
                    );
                }
            }
            $qb->andWhere($orX);
        }
        if (null !== $geoFilter) {
            $qb->andWhere(
                $qb->expr()->orX(
                    'ST_Intersects(ST_GeomFromText(:geometry), metadata.geometry) = true',
                    'ST_Intersects(ST_GeomFromText(:geometry), ST_GeomFromGML(dif.spatialExtentGeometry)) = true'
                )
            );
            $qb->setParameter('geometry', "SRID=4326;$geoFilter::geometry");
        }
        if (null !== $textFilter) {
            $searchProperties = array(
                'dataset.udi',
                'dif.title',
                'dif.abstract',
                'datasetSubmission.title',
                'datasetSubmission.abstract',
                'datasetSubmission.authors',
                'researchGroup.name',
            );
            $orX = null;
            $keywords = preg_split('/\s+/', $textFilter);
            foreach ($keywords as $index => $keyword) {
                foreach ($searchProperties as $searchProperty) {
                    $like = $qb->expr()->like(
                        $qb->expr()->lower($searchProperty),
                        ':keyword' . $index . str_replace('.', '_', $searchProperty)
                    );
                    if (null === $orX) {
                        $orX = $qb->expr()->orX($like);
                    } else {
                        $orX->add($like);
                    }
                    $qb->setParameter(
                        'keyword' . $index . str_replace('.', '_', $searchProperty),
                        '%' . strtolower($keyword) . '%'
                    );
                }
            }
            $qb->andWhere($orX);
        }
        $query = $qb->getQuery();
        return $query->getResult(Query::HYDRATE_ARRAY);
    }
}

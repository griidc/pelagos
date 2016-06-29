<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Doctrine\ORM\Query;

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
     * @Route("/datasets")
     * @Method("GET")
     *
     * @return Response
     */
    public function datasetsAction()
    {
        $datasets = array();
        $datasets['available'] = $this->getDatasets(
            array(
                'dataset.availabilityStatus' => array(
                    DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                    DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                )
            )
        );
        $datasets['restricted'] = $this->getDatasets(
            array(
                'dataset.availabilityStatus' => array(
                    DatasetSubmission::AVAILABILITY_STATUS_AVAILABLE_WITH_APPROVAL,
                    DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED,
                    DatasetSubmission::AVAILABILITY_STATUS_AVAILABLE_WITH_APPROVAL_REMOTELY_HOSTED,
                    DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED,
                )
            )
        );
        $datasets['inReview'] = $this->getDatasets(
            array(
                'dataset.availabilityStatus' => array(
                    DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL,
                )
            )
        );
        $datasets['identified'] = $this->getDatasets(
            array(
                'dataset.availabilityStatus' => array(
                    DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE,
                )
            )
        );

        return $this->render(
            'PelagosAppBundle:DataDiscovery:datasets.html.twig',
            array(
                'datasets' => $datasets,
            )
        );
    }

    /**
     * Get datasets with properties matching any values specified by $criteria.
     *
     * @param array $criteria An array of criteria.
     *
     * @return array
     */
    protected function getDatasets(array $criteria)
    {
        $qb = $this->get('doctrine.orm.entity_manager')
                   ->getRepository(Dataset::class)
                   ->createQueryBuilder('dataset');
        $qb->select('dataset, dif, datasetSubmission, metadata, researchGroup');
        $qb->join('dataset.dif', 'dif');
        $qb->join('dataset.datasetSubmission', 'datasetSubmission');
        $qb->join('dataset.metadata', 'metadata');
        $qb->join('dataset.researchGroup', 'researchGroup');
        foreach ($criteria as $property => $values) {
            foreach ($values as $value) {
                $qb->orWhere(
                    $qb->expr()->eq(
                        $property,
                        $qb->expr()->literal($value)
                    )
                );
            }
        }
        $query = $qb->getQuery();
        return $query->getResult(Query::HYDRATE_ARRAY);
    }
}

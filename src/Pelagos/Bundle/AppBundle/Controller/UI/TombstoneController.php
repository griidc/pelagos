<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

/**
 * The Dataset Tombstone controller.
 *
 * @Route("/tombstone")
 */
class TombstoneController extends UIController
{
    /**
     * The Dataland Page - dataset details per UDI.
     *
     * @param string $udi A UDI.
     *
     * @throws NotFoundHttpException When no non-available dataset is found with this UDI.
     *
     * @Route("/{udi}")
     *
     * @return Response
     */
    public function defaultAction($udi)
    {
        $dataset = $this->getDataset($udi);

        // Don't allow tombstones on available datasets.
        if (($dataset->getAvailabilityStatus() === DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE) or
            ($dataset->getAvailabilityStatus() === DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED)) {
            throw $this->createNotFoundException("No pending state placeholder found for UDI: $udi");
        }

        // Prevent webcrawling.
        $element = array(
            '#tag' => 'meta',
            '#attributes' => array(
                'name' => 'robots',
                'content' => 'noindex'
            )
        );
        drupal_add_html_head($element, 'meta');

        return $this->render(
            'PelagosAppBundle:Tombstone:index.html.twig',
            $twigData = array(
                'dataset' => $dataset,
            )
        );
    }

    /**
     * Get the Dataset for an UDI.
     *
     * @param string $udi The UDI to get the dataset for.
     *
     * @throws NotFoundHttpException When no dataset is found with this UDI.
     * @throws \Exception            When more than one dataset is found with this UDI.
     *
     * @return Dataset
     */
    protected function getDataset($udi)
    {
        $datasets = $this->entityHandler->getBy(Dataset::class, array('udi' => $udi));

        if (count($datasets) == 0) {
            throw $this->createNotFoundException("No dataset found for UDI: $udi");
        }

        if (count($datasets) > 1) {
            throw new \Exception("Got more than one return for UDI: $udi");
        }

        return $datasets[0];
    }
}

<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Entity\DatasetSubmission;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pelagos\Entity\Dataset;

/**
 * The Dataset Review controller for the Pelagos UI App Bundle.
 *
 * @Route("/dataset-review")
 */
class DatasetReviewController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * The default action for Dataset Review.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {
        $dataset = null;
        $udi = $request->query->get('udiReview');

        if ($udi !== null) {
            $datasets = $this->entityHandler
                ->getBy(Dataset::class, array('udi' => substr($udi, 0, 16)));
            if (count($datasets) == 1) {
                $dataset = $datasets[0];

                if ($dataset->getDatasetSubmission() === null) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        'The dataset ' . $udi . ' has not been submitted and cannot be loaded in review mode.'
                    );
                } elseif ($dataset->getDatasetSubmission()->getStatus() === DatasetSubmission::STATUS_INCOMPLETE) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        'The dataset ' . $udi . ' currently has a draft submission and cannot be loaded in review mode.'
                    );
                } elseif ($dataset->getMetadataStatus() === DatasetSubmission::METADATA_STATUS_BACK_TO_SUBMITTER) {
                    $request->getSession()->getFlashBag()->add(
                        'warning',
                        'The status of dataset ' . $udi . ' is Back To Submitter and cannot be loaded in review mode.'
                    );
                }
            } elseif (count($datasets) == 0) {
                $request->getSession()->getFlashBag()->add(
                    'warning',
                    'Sorry, the dataset with Unique Dataset Identifier (UDI) ' .
                    $udi . ' could not be found. Please email 
                    <a href="mailto:griidc@gomri.org?subject=REG Form">griidc@gomri.org</a> 
                    if you have any questions.'
                );
            }
        }

        return $this->render(
            'PelagosAppBundle:DatasetReview:index.html.twig',
            [
                'dataset' => $dataset,
                'udi' => $udi
            ]
        );
    }
}

<?php

namespace App\Controller\UI;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Handler\EntityHandler;

/**
 * The Dataset Tombstone controller.
 */
class TombstoneController extends AbstractController
{
    /**
     * An entity handler instance.
     *
     * @var EntityHandler
     */
    protected $entityHandler;

    /**
     * SideBySideController constructor.
     *
     * @param EntityHandler $entityHandler An entity handler instance.
     */
    public function __construct(EntityHandler $entityHandler)
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * The Dataland Page - dataset details per UDI.
     *
     * @param string $udi A UDI.
     *
     * @throws NotFoundHttpException When no non-available dataset is found with this UDI.
     *
     *
     * @return Response
     */
    #[Route(path: '/tombstone/{udi}', name: 'pelagos_app_ui_tombstone_default')]
    public function defaultAction(string $udi)
    {
        $dataset = $this->getDataset($udi);

        // Don't allow tombstones on available datasets.
        if (
            ($dataset->getAvailabilityStatus() === DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE) or
            ($dataset->getAvailabilityStatus() === DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED)
        ) {
            throw new NotFoundHttpException("No pending state placeholder found for UDI: $udi");
        }

        return $this->render(
            'Tombstone/index.html.twig',
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
     * @throws NotFoundHttpException When more than one dataset is found with this UDI.
     * @throws \Exception             When more than one dataset exists.
     *
     * @return Dataset
     */
    protected function getDataset(string $udi)
    {
        $datasets = $this->entityHandler->getBy(Dataset::class, array('udi' => $udi));

        if (count($datasets) == 0) {
            throw new NotFoundHttpException("No dataset found for UDI: $udi");
        }

        if (count($datasets) > 1) {
            throw new \Exception("Got more than one return for UDI: $udi");
        }

        return $datasets[0];
    }
}

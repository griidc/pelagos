<?php

namespace App\Controller\UI;

use App\Form\InspectFilesetType;
use App\Message\ExportFilesetMessage;
use App\Repository\DatasetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The inspect fileset tool helps to inspect a fileset on mimir.
 */
class InspectFilesetController extends AbstractController
{

    public function __construct(private DatasetRepository $datasetRepository, private MessageBusInterface $messageBus)
    {
    }

    /**
     * The default action for Initiate Fileset Review.
     *
     * @param Request $request The Symfony request object.
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/inspect-fileset', name: 'pelagos_app_ui_inspectfileset_default')]
    public function defaultAction(Request $request, FormFactoryInterface $formFactory)
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $form = $formFactory->createNamed(
            'inspectFileset',
            InspectFilesetType::class
        );

        $form->handleRequest($request);

        $udi = $form->getData()['datasetUdi'] ?? null;

        if ($form->isSubmitted() && $form->isValid()) {
            // enqueue export fileset message for async processing
            $dataset = $this->datasetRepository->findOneByUdi($udi);
            if ($dataset) {
                $submission = $dataset->getDatasetSubmission();
                if ($submission) {
                    $fileset = $submission->getFileset();
                    if ($fileset) {
                        $filesetId = $fileset->getId();
                        $exportFilesetMessage = new ExportFilesetMessage($filesetId);
                        $this->messageBus->dispatch($exportFilesetMessage);
                        return $this->render('InspectFileset/copy-started.html.twig', array('datasetUdi' => $udi));
                    }
                }
            }
        }

        return $this->render('InspectFileset/default.html.twig', array('form' => $form->createView()));
    }
}

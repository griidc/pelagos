<?php

namespace App\Controller\UI;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\Fileset;
use App\Form\InspectFilesetType;
use App\Message\ExportFilesetMessage;
use App\Repository\DatasetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The inspect fileset tool helps to inspect a fileset on mimir.
 */
class InspectFilesetController extends AbstractController
{
    /**
     * The default action for Initiate Fileset Review.
     */
    #[Route(path: '/inspect-fileset', name: 'pelagos_app_ui_inspectfileset_default')]
    public function defaultAction(Request $request, FormFactoryInterface $formFactory, DatasetRepository $datasetRepository, MessageBusInterface $messageBus): Response
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

        if ($form->isSubmitted()) {
            if ($udi === null) {
                $this->addFlash('error', 'Please enter a Dataset UDI.');
                return $this->render('InspectFileset/default.html.twig', array('form' => $form->createView()));
            }

            $dataset = $datasetRepository->findOneBy(['udi' => $udi]);

            // If not found, handle UDIs where the colon is replaced by a period
            if (!$dataset instanceof Dataset) {
                $normalizedUdi = $udi;
                if (!str_contains($normalizedUdi, ':') && str_contains($normalizedUdi, '.')) {
                    $lastDotPos = strrpos($normalizedUdi, '.');
                    if ($lastDotPos !== false) {
                        $normalizedUdi = substr_replace($normalizedUdi, ':', $lastDotPos, 1);
                    }
                }

                if ($normalizedUdi !== $udi) {
                    $dataset = $datasetRepository->findOneBy(['udi' => $normalizedUdi]);
                    // If found using normalized UDI, continue with normalized value
                    if ($dataset) {
                        $udi = $normalizedUdi;
                    }
                }
            }

            if (!$dataset instanceof Dataset) {
                $this->addFlash('error', sprintf('No dataset found for UDI "%s".', $udi));
                return $this->render('InspectFileset/default.html.twig', array('form' => $form->createView()));
            }

            $submission = $dataset->getDatasetSubmission();
            if (!$submission instanceof DatasetSubmission) {
                $this->addFlash('error', 'Dataset submission not found for the provided UDI.');
                return $this->render('InspectFileset/default.html.twig', array('form' => $form->createView()));
            }

            $fileset = $submission->getFileset();
            if (!$fileset instanceof Fileset) {
                $this->addFlash('error', 'No fileset is associated with this dataset.');
                return $this->render('InspectFileset/default.html.twig', array('form' => $form->createView()));
            }

            // enqueue export fileset message for async processing
            $exportFilesetMessage = new ExportFilesetMessage(
                $fileset->getId(),
                $this->getUser()->getPerson()->getEmailAddress()
            );
            $messageBus->dispatch($exportFilesetMessage);
            return $this->render('InspectFileset/copy-started.html.twig', array('datasetUdi' => $udi));
        }

        return $this->render('InspectFileset/default.html.twig', array('form' => $form->createView()));
    }
}

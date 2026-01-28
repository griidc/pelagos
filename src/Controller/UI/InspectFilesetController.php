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

        if ($form->isSubmitted()) {
            if (!$udi) {
                $this->addFlash('error', 'Please enter a Dataset UDI.');
                return $this->render('InspectFileset/default.html.twig', array('form' => $form->createView()));
            }

            $dataset = $this->datasetRepository->findOneByUdi($udi);

            // If not found, handle UDIs where the colon is replaced by a period
            if (!$dataset) {
                $normalizedUdi = $udi;
                if (!str_contains($normalizedUdi, ':') && str_contains($normalizedUdi, '.')) {
                    $lastDotPos = strrpos($normalizedUdi, '.');
                    if ($lastDotPos !== false) {
                        $normalizedUdi = substr_replace($normalizedUdi, ':', $lastDotPos, 1);
                    }
                }

                if ($normalizedUdi !== $udi) {
                    $dataset = $this->datasetRepository->findOneByUdi($normalizedUdi);
                    // If found using normalized UDI, continue with normalized value
                    if ($dataset) {
                        $udi = $normalizedUdi;
                    }
                }
            }

            if (!$dataset) {
                $this->addFlash('error', sprintf('No dataset found for UDI "%s".', $udi));
                return $this->render('InspectFileset/default.html.twig', array('form' => $form->createView()));
            }

            $submission = $dataset->getDatasetSubmission();
            if (!$submission) {
                $this->addFlash('error', 'Dataset submission not found for the provided UDI.');
                return $this->render('InspectFileset/default.html.twig', array('form' => $form->createView()));
            }

            $fileset = $submission->getFileset();
            if (!$fileset) {
                $this->addFlash('error', 'No fileset is associated with this dataset.');
                return $this->render('InspectFileset/default.html.twig', array('form' => $form->createView()));
            }

            // enqueue export fileset message for async processing
            $exportFilesetMessage = new ExportFilesetMessage(
                $fileset->getId(),
                $this->getUser()->getPerson()->getEmailAddress()
            );
            $this->messageBus->dispatch($exportFilesetMessage);
            return $this->render('InspectFileset/copy-started.html.twig', array('datasetUdi' => $udi));
        }

        return $this->render('InspectFileset/default.html.twig', array('form' => $form->createView()));
    }
}

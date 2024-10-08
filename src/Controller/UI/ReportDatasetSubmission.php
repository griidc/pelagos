<?php

namespace App\Controller\UI;

use App\Entity\Dataset;
use App\Entity\PersonDatasetSubmission;
use App\Form\DatasetSubmissionType;
use App\Form\PersonDatasetSubmissionType;
use App\Repository\DatasetRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * A controller for producing dataset reports.
 */
class ReportDatasetSubmission extends ReportController
{
    /**
     * Creates a CSV of the dataset fields by UDI.
     *
     * @param string            $udi               A UDI.
     * @param DatasetRepository $datasetRepository The dataset repository.
     *
     * @throws NotFoundHttpException When the dataset does not exist.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/dataset-submission-report/{udi}')]
    public function makeCSVAction(string $udi, DatasetRepository $datasetRepository, FormFactoryInterface $formFactory)
    {
        $dataset = $datasetRepository->findOneBy(['udi' => $udi]);

        if (!$dataset instanceof Dataset) {
            throw new NotFoundHttpException("Dataset $udi not found!");
        }

        $datasetSubmission = $dataset->getDatasetSubmission();

        $data[] = array(
            'label' => 'UDI',
            'value' => $udi,
        );

        $data[] = array(
            'label' => 'Submission Date',
            'value' => $datasetSubmission->getSubmissionTimeStamp()->format('c'),
        );

        $fields = array(
            'datasetContacts',
            'authors',
            'title',
            'abstract',
            'shortTitle',
            'purpose',
            'suppParams',
            'suppMethods',
            'suppInstruments',
            'suppSampScalesRates',
            'suppErrorAnalysis',
            'suppProvenance',
            'themeKeywords',
            'placeKeywords',
            'topicKeywords',
            'spatialExtent',
            'spatialExtentDescription',
            'temporalExtentNilReasonType',
            'temporalExtentDesc',
            'temporalExtentBeginPosition',
            'temporalExtentEndPosition',
            'restrictions',
        );

        $form = $formFactory->createNamed(
            '',
            DatasetSubmissionType::class,
            $datasetSubmission
        );

        foreach ($fields as $field) {
            if (!$form->offsetExists($field)) {
                continue;
            }

            $child = $form->get($field);
            $childView = $child->createView();
            $value = $childView->vars['value'];
            $label = $childView->vars['label'];

            if (empty($label)) {
                $label = $childView->vars['name'];
            }

            if (is_array($value)) {
                $value = implode(',', $value);
            }

            if (is_object($value)) {
                $sequence = 1;
                foreach ($value as $item) {
                    if ($item instanceof PersonDatasetSubmission) {
                        $prefix = "Contact[$sequence]-";

                        $contact = $formFactory->createNamed(
                            '',
                            PersonDatasetSubmissionType::class,
                            $item
                        );

                        $personView = $contact->get('person')->get('firstName')->createView();
                        $value = $personView->vars['value'];
                        $label = $personView->vars['label'];
                        $data[] = array(
                            'label' => $prefix . $label,
                            'value' => $value,
                        );

                        $personView = $contact->get('person')->get('lastName')->createView();
                        $value = $personView->vars['value'];
                        $label = $personView->vars['label'];
                        $data[] = array(
                            'label' => $prefix . $label,
                            'value' => $value,
                        );

                        $personView = $contact->get('person')->get('emailAddress')->createView();
                        $value = $personView->vars['value'];
                        $label = $personView->vars['label'];
                        $data[] = array(
                            'label' => $prefix . $label,
                            'value' => $value,
                        );

                        $roleView = $contact->get('role')->createView();
                        $value = $roleView->vars['value'];
                        $label = $roleView->vars['label'];
                        $data[] = array(
                            'label' => $prefix . $label,
                            'value' => $value,
                        );
                    } else {
                        $data[] = array(
                            'label' => $label,
                            'value' => "[object]",
                        );
                    }

                    $sequence++;
                }
            } else {
                $data[] = array(
                    'label' => $label,
                    'value' => $value,
                );
            }
        }

        return $this->writeCsvResponse(
            $data,
            "$udi-submission.csv"
        );
    }
}

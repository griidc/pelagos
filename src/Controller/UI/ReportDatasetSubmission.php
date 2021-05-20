<?php

namespace App\Controller\UI;

use App\Entity\DatasetSubmission;
use App\Form\DatasetSubmissionType;

use App\Repository\DatasetRepository;

use Doctrine\Common\Collections\Collection;


use Symfony\Component\Routing\Annotation\Route;

/**
 * A controller for a Report of Research Groups and related Datasets.
 *
 * @return Response A Symfony Response instance.
 */
class ReportDatasetSubmission extends ReportController
{
    /**
     * The default action for Dataset Review.
     *
     * @param string            $udi               A UDI.
     * @param DatasetRepository $datasetRepository The dataset repository.
     *
     * @Route("/overview/{udi}")
     *
     * @return Response A Response instance.
     */
    public function overviewAction(string $udi, DatasetRepository $datasetRepository)
    {
        $dataset = $datasetRepository->findOneBy(['udi' => $udi]);
        
        $datasetSubmission = $dataset->getDatasetSubmission();
        
        $data[] = array(
            'label' => 'UDI',
            'value' => $udi,
        );
        
        $fields = array(
            'title',
            'abstract',
            'authors',
            'restrictions',
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
            'distributionFormatName',
            'fileDecompressionTechnique',
            'datasetLinks',
            'datasetContacts',
            'metadataContacts',
            'distributionPoints',
            'remotelyHostedUrl',
            'isRemotelyHosted',
            'distributionPoints',
            'remotelyHostedUrl',
            'isRemotelyHosted',
            'remotelyHostedName',
            'remotelyHostedDescription',
            'remotelyHostedFunction',
            'isDatasetFileInColdStorage',
            'datasetFileColdStorageArchiveSize',
            'datasetFileColdStorageArchiveSha256Hash',
            'datasetFileColdStorageOriginalFilename',
        );
        
        
        
        $form = $this->get('form.factory')->createNamed(
            null,
            DatasetSubmissionType::class,
            $datasetSubmission
        );
        
        // dd($form->get('isDatasetFileInColdStorage'));
        
        foreach ($fields as $field) {
            $child = $form->get($field);
            $childView = $child->createView();
            $value = $childView->vars['value'];
            $label = $childView->vars['label'];
            
            if (empty($label)) {
                continue;
            }
            
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            
            if (is_object($value)) {
                $value = '[OBJECT]';
            }
            
            $data[] = array(
                'label' => $label,
                'value' => $value,
            );
        }
        
        
        // dd($data);
        
        return $this->writeCsvResponse(
            $data,
            'submission.csv'
        );
       
        
        // return $this->render(
            // 'DatasetReview/overview.html.twig',
            // array(
                // 'form' => $form->createView(),
            // )
        // );
    }
    
    
}
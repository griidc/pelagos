<?php

namespace App\Controller\Admin;

use App\Entity\DatasetSubmission;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

/**
 * @extends AbstractCrudController<DatasetSubmission>
 */
class DatasetSubmissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DatasetSubmission::class;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                IdField::new('id'),
                ChoiceField::new('status')
                    ->setChoices([
                        'Unsubmitted' => DatasetSubmission::STATUS_UNSUBMITTED,
                        'Incomplete' => DatasetSubmission::STATUS_INCOMPLETE,
                        'Complete' => DatasetSubmission::STATUS_COMPLETE,
                        'In Review' => DatasetSubmission::STATUS_IN_REVIEW,
                    ]),
                IntegerField::new('sequence'),
                TextField::new('title'),
                AssociationField::new('dataset'),
                TextField::new('creators'),
                DateField::new('modificationTimeStamp')->setLabel('Last Modified At')
                    ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
            ];
        }

        return [
            IdField::new('id'),
            ChoiceField::new('status')
                ->setChoices([
                    'Unsubmitted' => DatasetSubmission::STATUS_UNSUBMITTED,
                    'Incomplete' => DatasetSubmission::STATUS_INCOMPLETE,
                    'Complete' => DatasetSubmission::STATUS_COMPLETE,
                    'In Review' => DatasetSubmission::STATUS_IN_REVIEW,
                ]),
            AssociationField::new('datasetSubmissionReview'),
            IntegerField::new('sequence'),
            TextField::new('title'),
            TextField::new('shortTitle'),
            TextEditorField::new('abstract'),
            AssociationField::new('dataset'),

            TextField::new('authors'),
            TextField::new('pointOfContactName'),
            TextField::new('pointOfContactEmail'),
            ArrayField::new('datasetContacts'),
            ArrayField::new('metadataContacts'),
            ChoiceField::new('restrictions')
                ->setChoices(DatasetSubmission::RESTRICTIONS),
            ChoiceField::new('datasetFileTransferType')
                ->setChoices(DatasetSubmission::TRANSFER_TYPES),
            TextField::new('datasetFileUri'),
            TextField::new('largeFileUri'),
            ChoiceField::new('datasetFileTransferStatus')
                ->setChoices(DatasetSubmission::TRANSFER_STATUSES),
            TextField::new('datasetFileName'),
            IntegerField::new('datasetFileSize'),
            TextField::new('datasetFileSha256Hash'),
            IntegerField::new('datasetFileColdStorageArchiveSize'),
            TextField::new('datasetFileColdStorageArchiveSha256Hash'),
            TextField::new('datasetFileColdStorageOriginalFilename'),
            DateField::new('datasetFileUrlLastCheckedDate'),
            textfield::new('datasetFileUrlStatusCode'),
            ChoiceField::new('datasetStatus')
                ->setChoices([
                    'Accept Review' => DatasetSubmission::DATASET_ACCEPT_REVIEW,
                    'End Review' => DatasetSubmission::DATASET_END_REVIEW,
                    'Request Revisions' => DatasetSubmission::DATASET_REQUEST_REVISIONS,
                ]),
            TextField::new('suppParams'),
            TextField::new('suppMethods'),
            TextField::new('suppInstruments'),
            TextField::new('suppSampScalesRates'),
            TextField::new('suppErrorAnalysis'),
            TextField::new('suppProvenance'),
            ArrayField::new('themeKeywords'),
            ArrayField::new('placeKeywords'),
            ArrayField::new('topicKeywords'),
            ArrayField::new('keywords'),
            CodeEditorField::new('spatialExtent')
                ->setLanguage('xml')
                ->hideLineNumbers(true),
            TextField::new('spatialExtentDescription'),
            TextField::new('temporalExtentDesc'),
            DateField::new('temporalExtentBeginPosition'),
            DateField::new('temporalExtentEndPosition'),
            TextField::new('temporalExtentNilReasonType'),
            TextField::new('distributionFormatName'),
            TextField::new('fileDecompressionTechnique'),
            DateField::new('submissionTimeStamp'),
            AssociationField::new('submitter'),
            ArrayField::new('distributionPoints'),
            TextField::new('remotelyHostedName'),
            textfield::new('remotelyHostedDescription'),
            TextField::new('remotelyHostedFunction'),
            TextField::new('remotelyHostedUrl'),
            AssociationField::new('fileset'),
            ArrayField::new('datasetLinks'),
            IntegerField::new('coldStorageTotalUnpackedCount'),
            IntegerField::new('coldStorageTotalUnpackedSize'),
            TextField::new('additionalFunders'),





            AssociationField::new('creator')
                ->setLabel('Created By'),
            DateField::new('creationTimeStamp')
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz')
                ->setLabel('Creation Timestamp'),
            AssociationField::new('modifier')
                ->hideOnIndex()
                ->setLabel('Modified By'),
            DateField::new('modificationTimeStamp')
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz')
                ->setLabel('Modification Timestamp'),


            ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['modificationTimeStamp' => 'DESC'])
            ->setEntityLabelInPlural('Dataset Submissions')
            ->setEntityLabelInSingular('Dataset Submission')
            ->setPageTitle(Crud::PAGE_INDEX, 'Dataset Submissions')
            ->showEntityActionsInlined()
        ;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $exportAction = Action::new('export')
            ->setLabel('Export')
            ->setIcon('fa fa-file-export')
            ->linkToCrudAction('export')
            ->createAsGlobalAction();

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, $exportAction)
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fa fa-eye')
                    ->setLabel('View');
            })
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add(EntityFilter::new('dataset'))
            ->add(EntityFilter::new('creator'))
            ->add(DateTimeFilter::new('creationTimeStamp'))
            ->add(DateTimeFilter::new('modificationTimeStamp'))
        ;
    }
}

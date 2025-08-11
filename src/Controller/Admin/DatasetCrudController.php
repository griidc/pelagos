<?php

namespace App\Controller\Admin;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

/**
 * @extends AbstractCrudController<Dataset>
 */
class DatasetCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Dataset::class;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                IdField::new('id'),
                TextField::new('udi'),
                TextField::new('datasetLifecycleStatusString')
                    ->setLabel('Lifecycle Status'),
                TextField::new('title'),
                AssociationField::new('researchGroup'),
                AssociationField::new('datasetSubmission'),
                TextField::new('creators'),
                DateField::new('modificationTimeStamp')->setLabel('Last Modified At')
                    ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
            ];
        }


        return [
            IdField::new('id'),
            TextField::new('udi'),
            TextField::new('datasetLifecycleStatusString')
                    ->setLabel('Lifecycle Status'),
            TextField::new('title'),
            TextField::new('abstract'),
            AssociationField::new('doi'),
            AssociationField::new('researchGroup'),
            AssociationField::new('dif'),
            AssociationField::new('datasetSubmission'),
            CollectionField::new('datasetSubmissionHistory'),

            DateField::new('acceptedDate'),
            ChoiceField::new('identifiedStatus')
                ->setChoices([
                    'Unsubmitted' => DIF::STATUS_UNSUBMITTED,
                    'Submitted' => DIF::STATUS_SUBMITTED,
                    'Approved' => DIF::STATUS_APPROVED,
                ]),
            ChoiceField::new('datasetSubmissionStatus')
               ->setChoices([
                    'Unsubmitted' => DatasetSubmission::STATUS_UNSUBMITTED,
                    'Incomplete' => DatasetSubmission::STATUS_INCOMPLETE,
                    'Complete' => DatasetSubmission::STATUS_COMPLETE,
                    'In Review' => DatasetSubmission::STATUS_IN_REVIEW,
                ]),
            ChoiceField::new('datasetStatus')
                ->setChoices(Dataset::DATASET_STATUSES),
            ChoiceField::new('availabilityStatus')
                ->setChoices([
                    'Not Available' => DatasetSubmission::AVAILABILITY_STATUS_NOT_AVAILABLE,
                    'Pending Metadata Submission' => DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_SUBMISSION,
                    'Pending Metadata Approval' => DatasetSubmission::AVAILABILITY_STATUS_PENDING_METADATA_APPROVAL,
                    'Available' => DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED,
                    'Remotely Hosted' => DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                    'Restricted' => DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED,
                    'Publicly Available' => DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                ]),
            CollectionField::new('datasetPublications'),
            TextField::new('issueTrackingTicket'),
            CollectionField::new('funders'),


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
            ->setEntityLabelInPlural('Datasets')
            ->setEntityLabelInSingular('Dataset')
            ->setPageTitle(Crud::PAGE_INDEX, 'Datasets')
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
            ->add(EntityFilter::new('researchGroup'))
            ->add(EntityFilter::new('creator'))
            ->add(DateTimeFilter::new('creationTimeStamp'))
            ->add(DateTimeFilter::new('modificationTimeStamp'))
        ;
    }
}

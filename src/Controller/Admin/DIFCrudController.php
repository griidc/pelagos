<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\DIF;
use App\Entity\Funder;
use App\Entity\ResearchGroup;
use App\Filter\ResearchGroupFilter;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Easy Admin CRUD Controller for DIF Entity.
 *
 * @extends AbstractCrudController<DIF>
 */
#[AdminCrud(routePath: 'dif')]
#[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
class DIFCrudController extends AbstractCrudController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return DIF::class;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        $idField = IdField::new('id');

        $UdiField = AssociationField::new('dataset')
            ->setLabel('UDI');

        $UdiIndexField = TextField::new('dataset.udi')
            ->setLabel('UDI');

        $UdiEditField = TextField::new('dataset.udi')
            ->setLabel('UDI')
            ->setFormTypeOption('attr', ['readonly' => true]);

        $researchGroupField = TextField::new('researchGroup')
            ->setLabel('Project Title');

        $creatorField = AssociationField::new('creator')
            ->setLabel('Created By');

        $modifierField = AssociationField::new('modifier')
            ->hideOnIndex()
            ->setLabel('Modified By');

        $creationTimestampField = DateField::new('creationTimeStamp')
            ->setFormat('yyyy-MM-dd HH:mm:ss zzz')
            ->setLabel('Creation Timestamp');

        $modificationTimestampField = DateField::new('modificationTimeStamp')
            ->setFormat('yyyy-MM-dd HH:mm:ss zzz')
            ->setLabel('Modification Timestamp');

        $approvedDateField = DateField::new('approvedDate')
            ->hideOnIndex()
            ->setLabel('Approved Date');

        $fundersField = Field::new('funders')
            ->hideOnIndex()
            ->setLabel('Funder')
            ->setTemplatePath('@EasyAdmin/crud/field/array.html.twig')
            ->setFormType(EntityType::class)
            ->setFormTypeOption('class', Funder::class)
            ->setFormTypeOption('multiple', true)
            ->setFormTypeOption('attr', ['size' => 10])
            ->setFormTypeOption('required', false)
            ->setFormTypeOption('by_reference', true);

        $statusField = ChoiceField::new('status')
            ->setChoices([
                'Unsubmitted' => DIF::STATUS_UNSUBMITTED,
                'Submitted' => DIF::STATUS_SUBMITTED,
                'Approved' => DIF::STATUS_APPROVED,
            ])
            ->setLabel('Status');

        $isLockedField = BooleanField::new('isLocked')
            ->renderAsSwitch(false)
            ->setLabel('Locked');

        $primaryPointOfContactField = AssociationField::new('primaryPointOfContact')
            ->setLabel('Primary Data Point of Contact')
            ->hideOnIndex();

        $secondaryPointOfContactField = AssociationField::new('secondaryPointOfContact')
            ->setLabel('Additional Data Point of Contact')
            ->hideOnIndex();

        $additionalFundersField = TextField::new('additionalFunders')
            ->hideOnIndex()
            ->setLabel('Additional Funders');

        $titleField = TextField::new('title')
            ->setLabel('Dataset Title');

        $abstractField = TextareaField::new('abstract')
            ->setLabel('Dataset Abstract')
            ->hideOnIndex();

        $dataSizeField = ChoiceField::new('dataSize')
            ->setChoices(array_combine(DIF::DATA_SIZES, DIF::DATA_SIZES))
            ->hideOnIndex()
            ->setLabel('Approximate Dataset Size');

        $variablesObservedField = TextareaField::new('variablesObserved')
            ->hideOnIndex()
            ->setLabel('Data Parameters and Units');

        $estimatedStartDateField = DateField::new('estimatedStartDate')
            ->hideOnIndex()
            ->setLabel('Start Date');

        $estimatedEndDateField = DateField::new('estimatedEndDate')
            ->hideOnIndex()
            ->setLabel('End Date');

        $spatialExtentDescriptionField = TextareaField::new('spatialExtentDescription')
            ->hideOnIndex()
            ->setLabel('Geographic/Study Area Description');

        $nationalDataArchiveNODCField = BooleanField::new('nationalDataArchiveNODC')
            ->renderAsSwitch(false)
            ->hideOnIndex()
            ->setLabel('NODC');

        $nationalDataArchiveStoretField = BooleanField::new('nationalDataArchiveStoret')
            ->renderAsSwitch(false)
            ->hideOnIndex()
            ->setLabel('STORET');

        $nationalDataArchiveGBIFField = BooleanField::new('nationalDataArchiveGBIF')
            ->renderAsSwitch(false)
            ->hideOnIndex()
            ->setLabel('GBIF');

        $nationalDataArchiveNCBIField = BooleanField::new('nationalDataArchiveNCBI')
            ->renderAsSwitch(false)
            ->hideOnIndex()
            ->setLabel('NCBI');

        $nationalDataArchiveDataGovField = BooleanField::new('nationalDataArchiveDataGov')
            ->renderAsSwitch(false)
            ->hideOnIndex()
            ->setLabel('Data.gov');

        $nationalDataArchiveOtherField = TextField::new('nationalDataArchiveOther')
            ->hideOnIndex()
            ->setLabel('Other');

        $ethicalIssuesField = ChoiceField::new('ethicalIssues')
            ->setChoices([
                'Yes' => 'Yes',
                'No' => 'No',
                'Uncertain' => 'Uncertain',
            ])
            ->hideOnIndex()
            ->setLabel('Ethical Issues');

        $ethicalIssuesExplanationField = TextField::new('ethicalIssuesExplanation')
            ->hideOnIndex()
            ->setLabel('Ethical Issues Explanation');

        $remarksField = TextField::new('remarks')
            ->hideOnIndex()
            ->setLabel('Remarks');

        $issueTrackingTicketField = TextField::new('issueTrackingTicket')
            ->setLabel('Issue Tracking Ticket')
            ->hideOnIndex();

        // left for reference only as this will be used again when spatial extent geometry support is added back in the future
        /*
        $spatialExtentGeometryField = CodeEditorField::new('spatialExtentGeometry')
            ->hideOnIndex()
            ->hideOnDetail()
            ->hideOnForm()
            ->hideLineNumbers()
            ->setLanguage('xml')
            ->setLabel('Spatial Extent Geometry');
        */

        if (Crud::PAGE_EDIT === $pageName) {
            $idField = $idField->setDisabled();
            $statusField = $statusField->setDisabled();
            $isLockedField = $isLockedField->setDisabled();
            $researchGroupField = $researchGroupField->setDisabled();
            $creatorField = $creatorField->setDisabled();
            $modifierField = $modifierField->setDisabled();
            $creationTimestampField = $creationTimestampField->setDisabled();
            $modificationTimestampField = $modificationTimestampField->setDisabled();
            $approvedDateField = $approvedDateField->setDisabled();
        }

        $udiEditOrDetailField = Crud::PAGE_EDIT === $pageName ? $UdiEditField : $UdiIndexField;

        if (in_array($pageName, [Crud::PAGE_EDIT, Crud::PAGE_DETAIL], true)) {
            $fields = [
                FormField::addFieldset('Dataset Identification &amp; Status'),
                $idField,
                $udiEditOrDetailField,
                $statusField,
                $isLockedField,

                FormField::addFieldset('Dataset Contact'),
                $researchGroupField,
                $primaryPointOfContactField,
                $secondaryPointOfContactField,
                $fundersField,
                $additionalFundersField,

                FormField::addFieldset('Dataset Information'),
                $titleField,
                $abstractField,
                $dataSizeField,
                $variablesObservedField,
                $estimatedStartDateField,
                $estimatedEndDateField,
                $ethicalIssuesField,
                $ethicalIssuesExplanationField,

                FormField::addFieldset('National Datacenter'),
                $nationalDataArchiveNODCField,
                $nationalDataArchiveStoretField,
                $nationalDataArchiveGBIFField,
                $nationalDataArchiveNCBIField,
                $nationalDataArchiveDataGovField,
                $nationalDataArchiveOtherField,

                FormField::addFieldset('Dataset Extent'),
                $spatialExtentDescriptionField,

                FormField::addFieldset('DIF Curation Information'),
                $remarksField,
                $issueTrackingTicketField,
                $approvedDateField,
                $creatorField,
                $creationTimestampField,
                $modifierField,
                $modificationTimestampField,
            ];
        } else {
            $fields = [
                $idField,
                $UdiIndexField,
                $statusField,
                $isLockedField,
                $researchGroupField,
                $primaryPointOfContactField,
                $secondaryPointOfContactField,
                $fundersField,
                $additionalFundersField,
                $titleField,
                $abstractField,
                $dataSizeField,
                $variablesObservedField,
                $estimatedStartDateField,
                $estimatedEndDateField,
                $nationalDataArchiveNODCField,
                $nationalDataArchiveStoretField,
                $nationalDataArchiveGBIFField,
                $nationalDataArchiveNCBIField,
                $nationalDataArchiveDataGovField,
                $nationalDataArchiveOtherField,
                $ethicalIssuesField,
                $ethicalIssuesExplanationField,
                $spatialExtentDescriptionField,
                $remarksField,
                $issueTrackingTicketField,
                $approvedDateField,
                $creatorField,
                $creationTimestampField,
                $modifierField,
                $modificationTimestampField,
            ];
        }

        if (Crud::PAGE_EDIT === $pageName) {
            foreach ($fields as $field) {
                $field->setColumns(8);
            }
        }

        return $fields;
    }

    #[\Override]
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $researchGroups = $this->entityManager->getRepository(ResearchGroup::class)->getResearchGroupList();

        return parent::configureFilters($filters)
            ->add(ChoiceFilter::new('status')
                ->canSelectMultiple(true)
                ->setChoices([
                    'Unsubmitted' => DIF::STATUS_UNSUBMITTED,
                    'Submitted' => DIF::STATUS_SUBMITTED,
                    'Approved' => DIF::STATUS_APPROVED,
                ]))
            ->add(ResearchGroupFilter::new('researchGroup')
                ->setChoices($researchGroups)
                ->canSelectMultiple(true))
            ->add('creator')
            ->add('creationTimeStamp')
            ->add('modificationTimeStamp')
        ;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setSearchFields(['dataset.udi', 'title', 'dataset.title'])
            ->setDefaultSort(['modificationTimeStamp' => 'DESC'])
            ->setEntityLabelInPlural('DIFs')
            ->setEntityLabelInSingular('DIF')
            ->setPageTitle(Crud::PAGE_INDEX, 'DIFs')
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

        $approveDifAction = Action::new('approveDif')
            ->setLabel('Approve')
            ->setIcon('fa fa-check')
            ->linkToCrudAction('approveDif')
            ->addCssClass('btn btn-secondary');

        $submitDifAction = Action::new('submitDif')
            ->setLabel('Submit')
            ->setIcon('fa fa-arrow-up')
            ->linkToCrudAction('submitDif')
            ->addCssClass('btn btn-secondary');

        $unlockDifAction = Action::new('unlockDif')
            ->setLabel('Unlock')
            ->setIcon('fa fa-unlock')
            ->linkToCrudAction('unlockDif')
            ->addCssClass('btn btn-secondary');

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, $exportAction)
            ->add(Crud::PAGE_EDIT, $submitDifAction)
            ->add(Crud::PAGE_EDIT, $approveDifAction)
            ->add(Crud::PAGE_EDIT, $unlockDifAction)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, static function (Action $action): Action {
                return $action
                    ->setLabel('Save Changes')
                    ->setIcon('fa fa-save')
                    ->addCssClass('btn btn-secondary');
            })
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fa fa-eye')
                    ->setLabel('View');
            })
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }

    public function submitDif(AdminContext $context): RedirectResponse
    {
        return $this->runDifTransition(
            context: $context,
            transition: static function (DIF $dif): void {
                $dif->submit();
            },
            successMessage: 'DIF submitted.',
        );
    }

    public function approveDif(AdminContext $context): RedirectResponse
    {
        return $this->runDifTransition(
            context: $context,
            transition: static function (DIF $dif): void {
                $dif->approve();
            },
            successMessage: 'DIF approved.',
        );
    }

    public function unlockDif(AdminContext $context): RedirectResponse
    {
        return $this->runDifTransition(
            context: $context,
            transition: static function (DIF $dif): void {
                $dif->unlock();
            },
            successMessage: 'DIF unlocked.',
        );
    }

    public function export(AdminContext $context): RedirectResponse
    {
        return $this->runDifTransition(
            context: $context,
            transition: static function (): void {
            },
            successMessage: 'DIF exported stub - export functionality not yet implemented.',
        );
    }

    /**
     * Execute DIF transition actions with common flash and redirect behavior.
     */
    private function runDifTransition(AdminContext $context, callable $transition, string $successMessage): RedirectResponse
    {
        /** @var DIF $dif */
        $dif = $context->getEntity()->getInstance();

        try {
            $transition($dif);
            $this->entityManager->flush();
            $this->addFlash('success', $successMessage);
        } catch (\Throwable $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        $request = $context->getRequest();
        $redirectUrl = $context->getReferrer() ?? $request->headers->get('referer') ?? $request->getUri();

        return $this->redirect($redirectUrl);
    }
}

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
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
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

    public static function getEntityFqcn(): string
    {
        return DIF::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $idField = IdField::new('id');
        $datasetAssociationField = AssociationField::new('dataset')
            ->setLabel('Dataset');
        $researchGroupField = TextField::new('researchGroup')
            ->setLabel('Research Group')
            ->hideOnIndex();
        $creatorField = AssociationField::new('creator')
            ->setLabel('Created By')
            ->hideOnIndex();
        $modifierField = AssociationField::new('modifier')
            ->hideOnIndex()
            ->setLabel('Modified By')
            ->hideOnIndex();
        $creationTimestampField = DateField::new('creationTimeStamp')
            ->setFormat('yyyy-MM-dd HH:mm:ss zzz')
            ->setLabel('Creation Timestamp')
            ->hideOnIndex();
        $modificationTimestampField = DateField::new('modificationTimeStamp')
            ->setFormat('yyyy-MM-dd HH:mm:ss zzz')
            ->setLabel('Last Modified At');
        $approvedDateField = DateField::new('approvedDate')
            ->hideOnIndex()
            ->setLabel('Approved Date');
        $fundersField = Field::new('funders')
            ->hideOnIndex()
            ->setLabel('Funders (hold control to select multiple)')
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

        if (Crud::PAGE_EDIT === $pageName) {
            $idField = $idField->setDisabled();
            $datasetAssociationField = $datasetAssociationField->setDisabled();
            $statusField = $statusField->setDisabled();
            $isLockedField = $isLockedField->setDisabled();
            $researchGroupField = $researchGroupField->setDisabled();
            $creatorField = $creatorField->setDisabled();
            $modifierField = $modifierField->setDisabled();
            $creationTimestampField = $creationTimestampField->setDisabled();
            $modificationTimestampField = $modificationTimestampField->setDisabled();
            $approvedDateField = $approvedDateField->setDisabled();
        }

        $fields = [
            $idField,
            $datasetAssociationField,
            TextField::new('title'),
            $statusField,
            $isLockedField,
            $researchGroupField,
            $fundersField,
            TextField::new('additionalFunders')
                ->hideOnIndex()
                ->setLabel('Additional Funders'),
            AssociationField::new('primaryPointOfContact')
                ->hideOnIndex(),
            AssociationField::new('secondaryPointOfContact')
                ->hideOnIndex(),
            TextareaField::new('abstract')
                ->hideOnIndex()
                ->setLabel('Abstract'),
            BooleanField::new('fieldOfStudyEcologicalBiological')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('Ecological/Biological Field of Study'),
            BooleanField::new('fieldOfStudyPhysicalOceanography')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('Physical Oceanography Field of Study'),
            BooleanField::new('fieldOfStudyAtmospheric')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('Atmospheric Field of Study'),
            BooleanField::new('fieldOfStudyChemical')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('Chemical Field of Study'),
            BooleanField::new('fieldOfStudyHumanHealth')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('Human Health Field of Study'),
            BooleanField::new('fieldOfStudySocialCulturalPolitical')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('Social/Cultural/Political Field of Study'),
            BooleanField::new('fieldOfStudyEconomics')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('Economics Field of Study'),
            TextField::new('fieldOfStudyOther')
                ->hideOnIndex()
                ->setLabel('Other Field of Study'),
            ChoiceField::new('dataSize')
                ->setChoices(array_combine(DIF::DATA_SIZES, DIF::DATA_SIZES))
                ->hideOnIndex()
                ->setLabel('Approximate Dataset Size'),
            TextareaField::new('variablesObserved')
                ->hideOnIndex()
                ->setLabel('Variables Observed'),
            BooleanField::new('collectionMethodFieldSampling')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('Collection Method: Field Sampling'),
            BooleanField::new('collectionMethodSimulatedGenerated')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('Collection Method: Simulated/Generated'),
            BooleanField::new('collectionMethodRemoteSensing')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('Collection Method: Remote Sensing'),
            TextField::new('collectionMethodOther')
                ->hideOnIndex()
                ->setLabel('Collection Method: Other'),
            DateField::new('estimatedStartDate')
                ->hideOnIndex()
                ->setLabel('Estimated Start Date'),
            DateField::new('estimatedEndDate')
                ->hideOnIndex()
                ->setLabel('Estimated End Date'),
            TextareaField::new('spatialExtentDescription')
                ->hideOnIndex()
                ->setLabel('Spatial Extent Description'),
            CodeEditorField::new('spatialExtentGeometry')
                ->hideOnIndex()
                ->hideLineNumbers()
                ->setLanguage('xml')
                ->setLabel('Spatial Extent Geometry'),
            BooleanField::new('nationalDataArchiveNODC')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('National Data Archive: NODC'),
            BooleanField::new('nationalDataArchiveStoret')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('National Data Archive: STORET'),
            BooleanField::new('nationalDataArchiveGBIF')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('National Data Archive: GBIF'),
            BooleanField::new('nationalDataArchiveNCBI')
                ->renderAsSwitch(false)
                ->hideOnIndex()
                ->setLabel('National Data Archive: NCBI'),
            TextField::new('nationalDataArchiveOther')
                ->hideOnIndex()
                ->setLabel('National Data Archive: Other'),
            ChoiceField::new('ethicalIssues')
                ->setChoices([
                    'Yes' => 'Yes',
                    'No' => 'No',
                    'Uncertain' => 'Uncertain',
                ])
                ->hideOnIndex()
                ->setLabel('Ethical Issues'),
            TextField::new('ethicalIssuesExplanation')
                ->hideOnIndex()
                ->setLabel('Ethical Issues Explanation'),
            textfield::new('remarks')
                ->hideOnIndex()
                ->setLabel('Remarks'),
            $approvedDateField,
            CollectionField::new('keywords')
                ->hideOnIndex()
                ->hideWhenUpdating()
                ->setLabel('Keywords'),
            $creatorField,
            $creationTimestampField,
            $modifierField,
            $modificationTimestampField,
        ];

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
        if (!$entityInstance instanceof DIF) {
            parent::updateEntity($entityManager, $entityInstance);

            return;
        }

        if ($entityInstance->isSubmittable()) {
            $entityInstance->submit();
        } elseif ($entityInstance->isApprovable()) {
            $entityInstance->approve();
        }

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

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
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
            ->setLabel('Approve DIF')
            ->setIcon('fa fa-check')
            ->linkToCrudAction('approveDif')
            ->addCssClass('btn btn-secondary');

        $submitDifAction = Action::new('submitDif')
            ->setLabel('Unsubmitted -> Submitted')
            ->setIcon('fa fa-lock')
            ->linkToCrudAction('submitDif')
            ->addCssClass('btn btn-secondary');

        $unlockDifAction = Action::new('unlockDif')
            ->setLabel('Unlock DIF')
            ->setIcon('fa fa-unlock')
            ->linkToCrudAction('unlockDif')
            ->addCssClass('btn btn-secondary');

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, $exportAction)
            ->add(Crud::PAGE_EDIT, $submitDifAction)
            ->add(Crud::PAGE_EDIT, $approveDifAction)
            ->add(Crud::PAGE_EDIT, $unlockDifAction)
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fa fa-eye')
                    ->setLabel('View');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fa fa-edit')
                    ->setLabel('Edit');
            })
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT);
    }

    public function submitDif(AdminContext $context): RedirectResponse
    {
        return $this->runDifTransition(
            $context,
            static function (DIF $dif): void {
                $dif->submit();
            },
            'DIF submitted.'
        );
    }

    public function approveDif(AdminContext $context): RedirectResponse
    {
        return $this->runDifTransition(
            $context,
            static function (DIF $dif): void {
                $dif->approve();
            },
            'DIF approved.'
        );
    }

    public function unlockDif(AdminContext $context): RedirectResponse
    {
        return $this->runDifTransition(
            $context,
            static function (DIF $dif): void {
                $dif->unlock();
            },
            'DIF unlocked.'
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

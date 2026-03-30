<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\DIF;
use App\Entity\Funder;
use App\Entity\ResearchGroup;
use App\Filter\ResearchGroupFilter;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
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
        /** @var DIF|null $dif */
        $dif = $this->getContext()?->getEntity()->getInstance();

        $idField = IdField::new('id');


        $UdiIndexField = TextField::new('dataset.udi')
            ->setLabel('UDI');

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
            ->setFormTypeOption('choice_label', 'name')
            ->setFormTypeOption('multiple', true)
            ->setFormTypeOption('attr', ['data-ea-widget' => 'ea-autocomplete'])
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
            ->setFormTypeOption('query_builder', $this->createPointOfContactQueryBuilder($dif))
            ->hideOnIndex();

        $secondaryPointOfContactField = AssociationField::new('secondaryPointOfContact')
            ->setLabel('Additional Data Point of Contact')
            ->setFormTypeOption('query_builder', $this->createPointOfContactQueryBuilder($dif))
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

        if (Crud::PAGE_DETAIL === $pageName) {
            $fields = [
                FormField::addFieldset('Dataset Identification &amp; Status'),
                $idField,
                $UdiIndexField,
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

        return $fields;
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

    /**
     * Restrict point-of-contact to those in the DIF's research group.
     */
    private function createPointOfContactQueryBuilder(?DIF $dif): ?callable
    {
        $researchGroup = $dif?->getDataset()?->getResearchGroup();

        if (null === $researchGroup) {
            return null;
        }

        $researchGroupId = $researchGroup->getId();

        return static function (PersonRepository $personRepository) use ($researchGroupId) {
            return $personRepository->createQueryBuilder('entity')
                ->innerJoin('entity.personResearchGroups', 'personResearchGroup')
                ->innerJoin('personResearchGroup.researchGroup', 'researchGroup')
                ->andWhere('researchGroup.id = :researchGroupId')
                ->setParameter('researchGroupId', $researchGroupId)
                ->orderBy('entity.lastName', 'ASC')
                ->addOrderBy('entity.firstName', 'ASC')
            ;
        };
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

        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX, $exportAction)
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
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
}

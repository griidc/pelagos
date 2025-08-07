<?php

namespace App\Controller\Admin;

use App\Entity\DIF;
use App\Entity\ResearchGroup;
use App\Filter\ResearchGroupFilter;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Component\Validator\Constraints\Choice;

/**
 * Easy Admin CRUD Controller for DIF Entity.
 *
 * @extends AbstractCrudController<DIF>
 */
#[AdminCrud(routePath: 'dif')]
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
        return [
            IdField::new('id'),
            TextField::new('udi')->setLabel('UDI'),
            TextField::new('title'),
            ChoiceField::new('status')
                ->setChoices([
                    'Unsubmitted' => DIF::STATUS_UNSUBMITTED,
                    'Submitted' => DIF::STATUS_SUBMITTED,
                    'Approved' => DIF::STATUS_APPROVED,
                ]),

            BooleanField::new('isLocked')
                ->renderAsSwitch(false)
                ->setLabel('Locked'),

            TextField::new('researchGroup')
                ->setLabel('Research Group'),

            CollectionField::new('Funders')
                ->hideOnIndex()
                ->setLabel('Funders'),
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
                ->hideOnIndex()
                ->setLabel('Ecological/Biological Field of Study'),
            BooleanField::new('fieldOfStudyPhysicalOceanography')
                ->hideOnIndex()
                ->setLabel('Physical Oceanography Field of Study'),
            BooleanField::new('fieldOfStudyAtmospheric')
                ->hideOnIndex()
                ->setLabel('Atmospheric Field of Study'),
            BooleanField::new('fieldOfStudyChemical')
                ->hideOnIndex()
                ->setLabel('Chemical Field of Study'),
            BooleanField::new('fieldOfStudyHumanHealth')
                ->hideOnIndex()
                ->setLabel('Human Health Field of Study'),
            BooleanField::new('fieldOfStudySocialCulturalPolitical')
                ->hideOnIndex()
                ->setLabel('Social/Cultural/Political Field of Study'),
            BooleanField::new('fieldOfStudyEconomics')
                ->hideOnIndex()
                ->setLabel('Economics Field of Study'),
            BooleanField::new('fieldOfStudyOther')
                ->hideOnIndex()
                ->setLabel('Other Field of Study'),
            TextField::new('dataSize')
                ->hideOnIndex()
                ->setLabel('Data Size'),
            TextareaField::new('variablesObserved')
                ->hideOnIndex()
                ->setLabel('Variables Observed'),
            BooleanField::new('collectionMethodFieldSampling')
                ->hideOnIndex()
                ->setLabel('Collection Method: Field Sampling'),
            BooleanField::new('collectionMethodSimulatedGenerated')
                ->hideOnIndex()
                ->setLabel('Collection Method: Simulated/Generated'),
            BooleanField::new('collectionMethodRemoteSensing')
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
                ->setLabel('Spatial Extent Geometry'),
            BooleanField::new('nationalDataArchiveNODC')
                ->hideOnIndex()
                ->setLabel('National Data Archive: NODC'),
            BooleanField::new('nationalDataArchiveStoret')
                ->hideOnIndex()
                ->setLabel('National Data Archive: STORET'),
            BooleanField::new('nationalDataArchiveGBIF')
                ->hideOnIndex()
                ->setLabel('National Data Archive: GBIF'),
            BooleanField::new('nationalDataArchiveNCBI')
                ->hideOnIndex()
                ->setLabel('National Data Archive: NCBI'),
            TextField::new('nationalDataArchiveOther')
                ->hideOnIndex()
                ->setLabel('National Data Archive: Other'),
            TextField::new('ethicalIssues')
                ->hideOnIndex()
                ->setLabel('Ethical Issues'),
            TextField::new('ethicalIssuesExplanation')
                ->hideOnIndex()
                ->setLabel('Ethical Issues Explanation'),
            textfield::new('remarks')
                ->hideOnIndex()
                ->setLabel('Remarks'),
            DateField::new('approvedDate')
                ->hideOnIndex()
                ->setLabel('Approved Date'),

            CollectionField::new('keywords')
                ->hideOnIndex()
                ->setLabel('Keywords'),


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

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        $researchGroupRepository = $this->entityManager->getRepository(ResearchGroup::class);

        $researchGroups = $researchGroupRepository->getResearchGroupList();

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
            ->add(ChoiceFilter::new('isLocked')
                ->setChoices([
                    'Yes' => true,
                    'No' => false,
                ]))
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
}

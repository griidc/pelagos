<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\FundingCycle;
use App\Form\ResearchGroupType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @extends AbstractCrudController<FundingCycle>
 */
#[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
class FundingCycleCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return FundingCycle::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['modificationTimeStamp' => 'DESC'])
            ->setEntityLabelInPlural('Funding Cycles')
            ->setEntityLabelInSingular('Funding Cycle')
            ->setPageTitle(Crud::PAGE_INDEX, 'Funding Cycles')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Funding Cycle')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Funding Cycle')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Funding Cycle Details')
            ->showEntityActionsInlined();
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
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
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Delete')
                    ->displayIf(function (FundingCycle $fundingCycle) {
                        return $fundingCycle->isDeletable();
                    });
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Delete')
                    ->displayIf(function (FundingCycle $fundingCycle) {
                        return $fundingCycle->isDeletable();
                    });
            });
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            TextField::new('udiPrefix'),
            AssociationField::new('fundingOrganization'),
            TextareaField::new('description')->hideOnIndex(),
            UrlField::new('url'),
            DateField::new('startDate')->hideOnIndex(),
            DateField::new('endDate')->hideOnIndex(),
            CollectionField::new('researchGroups')
                ->setDisabled()
                ->setEntryType(ResearchGroupType::class)
                ->setEntryIsComplex(true)
                ->hideOnIndex(),
            DateField::new('creationTimeStamp')->setLabel('Created At')
                ->onlyOnDetail()
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
            AssociationField::new('creator')->setLabel('Created By')
                ->onlyOnDetail()
                ->setTemplateName('crud/field/generic'),
            DateField::new('modificationTimeStamp')->setLabel('Last Modified At')
                ->onlyOnDetail()
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
            AssociationField::new('modifier')->setLabel('Last Modified By')
                ->onlyOnDetail()
                ->setTemplateName('crud/field/generic'),
        ];
    }
}

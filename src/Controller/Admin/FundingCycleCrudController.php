<?php

namespace App\Controller\Admin;

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
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @extends AbstractCrudController<FundingCycle>
 */
class FundingCycleCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return FundingCycle::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Funding Cycles')
            ->setEntityLabelInSingular('Funding Cycle')
            ->setPageTitle(Crud::PAGE_INDEX, 'Funding Cycles')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Funding Cycle')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Funding Cycle')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Funding Cycle Details')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
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
            });
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            TextField::new('udiPrefix'),
            AssociationField::new('fundingOrganization'),
            TextEditorField::new('description')->hideOnIndex(),
            UrlField::new('url'),
            DateField::new('startDate')->hideOnIndex(),
            DateField::new('endDate')->hideOnIndex(),
            CollectionField::new('researchGroups')
                ->setDisabled()
                ->setEntryType(ResearchGroupType::class)
                ->setEntryIsComplex(true)
                ->hideOnIndex(),
        ];
    }
}

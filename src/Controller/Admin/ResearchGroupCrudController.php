<?php

namespace App\Controller\Admin;

use App\Entity\PersonResearchGroup;
use App\Entity\ResearchGroup;
use App\Repository\ResearchGroupRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @extends AbstractCrudController<ResearchGroup>
 */
class ResearchGroupCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

    public static function getEntityFqcn(): string
    {
        return ResearchGroup::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['modificationTimeStamp' => 'DESC'])
            ->setEntityLabelInPlural('Research Groups')
            ->setEntityLabelInSingular('Research Group')
            ->setPageTitle(Crud::PAGE_INDEX, 'Research Groups')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Research Group')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Research Group')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Research Group Details')
            ->showEntityActionsInlined()
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
        ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
            return $action
                ->setIcon('fa fa-edit')
                ->setLabel('Edit');
        })
        ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
            return $action
                ->setIcon('fa fa-eye')
                ->setLabel('View');
        })
        ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
            return $action
                ->setIcon('fa fa-trash')
                ->setLabel('Delete')
                ->displayIf(function (ResearchGroup $researchGroup) {
                    return !$this->isResearchGroupInUse($researchGroup);
                });
        });
        ;
    }

    /**
     * Configure fields for EZAdmin CRUD Controller.
     *
     * @param string $pageName default param for parent method (not used)
     */
    public function configureFields(string $pageName): iterable
    {
        /** @var ResearchGroup $researchGroup */
        $researchGroup = $this->getContext()?->getEntity()->getInstance();
        $personResearchGroup = new PersonResearchGroup();
        $personResearchGroup->setResearchGroup($researchGroup);

        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name'),
            TextField::new('shortName')->onlyOnForms(),
            AssociationField::new('fundingCycle'),
            TextField::new('fundingOrganization')->setDisabled(),
            TelephoneField::new('phoneNumber')->hideOnIndex(),
            UrlField::new('url')->hideOnIndex(),
            TextareaField::new('deliveryPoint')->hideOnIndex(),
            TextField::new('city')->hideOnIndex(),
            TextField::new('administrativeArea')->hideOnIndex()->setLabel('State'),
            TextField::new('postalCode')->hideOnIndex(),
            TextField::new('country')->hideOnIndex(),
            TextareaField::new('description')->hideOnIndex(),
            EmailField::new('emailAddress')->hideOnIndex(),
            BooleanField::new('locked')->renderAsSwitch(false)->setLabel('Closed Out'),
            CollectionField::new('personResearchGroups')
            ->setFormTypeOptions([
               'prototype' => true,
               'prototype_data' => $personResearchGroup,
            ])
            ->hideOnIndex()
            ->hideWhenCreating()
            ->useEntryCrudForm(),
            CollectionField::new('datasets')->setDisabled()->hideOnIndex()->hideWhenCreating(),
        ];
    }

    /**
     * Is this Research Group in use on an Information Product.
     */
    private function isResearchGroupInUse(ResearchGroup $researchGroup): bool
    {
        return $researchGroup->getDatasets()->count() > 0 or $researchGroup->getPersonResearchGroups()->count() > 0;
    }
}

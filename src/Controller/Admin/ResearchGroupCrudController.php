<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\PersonResearchGroup;
use App\Entity\ResearchGroup;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @extends AbstractCrudController<ResearchGroup>
 */
#[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
class ResearchGroupCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return ResearchGroup::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['modificationTimeStamp' => 'DESC'])
            ->setEntityLabelInPlural('Research Groups')
            ->setEntityLabelInSingular('Research Group')
            ->setPageTitle(Crud::PAGE_INDEX, 'Research Groups')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Research Group')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Research Group')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Research Group Details')
            ->showEntityActionsInlined()
        ;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
        ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
        ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action
                ->setIcon('fa fa-plus-circle')
                ->setLabel('Create New Research Group');
        })
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
                ->displayIf(function (ResearchGroup $researchGroup) {
                    return !$this->isResearchGroupInUse($researchGroup);
                });
        })
        ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
            return $action
                ->setIcon('fa fa-trash')
                ->setLabel('Delete')
                ->displayIf(function (ResearchGroup $researchGroup) {
                    return !$this->isResearchGroupInUse($researchGroup);
                });
        })
        ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action
                ->setIcon('fa fa-save')
                ->setLabel('Save and Close');
        });
    }

    #[\Override]
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
            TextField::new('fundingOrganization')->setDisabled()->hideWhenCreating(),
            CollectionField::new('personResearchGroups')
            ->setFormTypeOptions([
               'prototype' => true,
               'prototype_data' => $personResearchGroup,
            ])
            ->hideOnIndex()
            ->hideWhenCreating()
            ->useEntryCrudForm(),
            UrlField::new('url')->hideOnIndex(),
            TextareaField::new('deliveryPoint')->hideOnIndex(),
            TextField::new('city')->hideOnIndex(),
            TextField::new('administrativeArea')->hideOnIndex()->setLabel('State'),
            TextField::new('postalCode')->hideOnIndex(),
            TextField::new('country')->hideOnIndex(),
            TextareaField::new('description')->hideOnIndex(),
            EmailField::new('emailAddress')->hideOnIndex(),
            TelephoneField::new('phoneNumber')->hideOnIndex(),
            BooleanField::new('locked')->renderAsSwitch(false)->setLabel('Closed Out'),
            CollectionField::new('datasets')->setDisabled()->hideOnIndex()->hideWhenCreating(),
            DateField::new('creationTimeStamp')->setLabel('Created At')
                ->onlyOnDetail()
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
            TextField::new('creator')->setLabel('Created By')
                ->onlyOnDetail(),
            DateField::new('modificationTimeStamp')->setLabel('Last Modified At')
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz')
                ->hideOnForm(),
            TextField::new('modifier')->setLabel('Last Modified By')
                ->onlyOnDetail(),
        ];
    }

    /**
     * Is this Research Group in use on an Information Product.
     */
    private function isResearchGroupInUse(ResearchGroup $researchGroup): bool
    {
        return $researchGroup->getDatasets()->count() > 0 or $researchGroup->getPersonResearchGroups()->count() > 0;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addJsFile(Asset::new('build/js/person-research-group-pods.js')->defer());
    }
}

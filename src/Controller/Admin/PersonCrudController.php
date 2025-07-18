<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\Person;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
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
 * @extends AbstractCrudController<Person>
 */
#[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
class PersonCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['modificationTimeStamp' => 'DESC'])
            ->setEntityLabelInPlural('People')
            ->setEntityLabelInSingular('Person')
            ->setPageTitle(Crud::PAGE_INDEX, 'People')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Person')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Person')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Person Details')
            ->showEntityActionsInlined()
        ;
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
                    ->displayIf(function (Person $person) {
                        return $person->isDeletable();
                    });
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Delete')
                    ->displayIf(function (Person $person) {
                        return $person->isDeletable();
                    });
            })
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('firstName'),
            TextField::new('lastName'),
            EmailField::new('emailAddress'),
            TelephoneField::new('phoneNumber')->hideOnIndex(),
            TextareaField::new('deliveryPoint')->hideOnIndex(),
            TextField::new('city')->hideOnIndex(),
            TextField::new('administrativeArea')->setLabel('State')->hideOnIndex(),
            TextField::new('postalCode')->hideOnIndex(),
            TextField::new('country')->hideOnIndex(),
            UrlField::new('url')->hideOnIndex(),
            TextField::new('organization'),
            TextField::new('position')->hideOnIndex(),
            ArrayField::new('fundingOrganizations')
                ->hideOnIndex()
                ->setDisabled(),
            ArrayField::new('FundingCycles')
                ->hideOnIndex()
                ->setDisabled(),
            ArrayField::new('ResearchGroupNames')->setLabel('Research Groups')
                ->hideOnIndex()
                ->setDisabled()
                ->setColumns(40),
            AssociationField::new('account')
                ->hideOnIndex()
                ->setDisabled(),
            ArrayField::new('Datasets')
                ->hideOnIndex()
                ->setDisabled(),
            ArrayField::new('Publications')
                ->hideOnIndex()
                ->setDisabled(),
            DateField::new('creationTimeStamp')->setLabel('Created At')
                ->onlyOnDetail()
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
            TextField::new('creator')->setLabel('Created By')
                ->onlyOnDetail(),
            DateField::new('modificationTimeStamp')->setLabel('Last Modified At')
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz')
                ->setDisabled(),
            TextField::new('modifier')->setLabel('Last Modified By')
                ->onlyOnDetail(),
        ];
    }
}

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
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
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

    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['modificationTimeStamp' => 'DESC'])
            ->setEntityLabelInPlural('People')
            ->setEntityLabelInSingular('Person')
            ->setPageTitle(Crud::PAGE_INDEX, 'People')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Person')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Person')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Person Details')
            ->showEntityActionsInlined()
        ;
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
        })
        ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
            return $action
                ->setIcon('fa fa-trash')
                ->setLabel('Delete')
                ->displayIf(function (Person $person) {
                    return $person->isDeletable();
                });
        });
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('firstName'),
            TextField::new('lastName'),
            EmailField::new('emailAddress'),
            TelephoneField::new('phoneNumber')->hideOnIndex(),
            TextareaField::new('deliveryPoint')->hideOnIndex(),
            TextField::new('city'),
            TextField::new('administrativeArea'),
            TextField::new('postalCode')->hideOnIndex(),
            TextField::new('country'),
            UrlField::new('url'),
            TextField::new('organization'),
            TextField::new('position'),
            AssociationField::new('account')
                ->setDisabled(),
            CollectionField::new('personFundingOrganizations')
                ->hideOnIndex()
                ->setDisabled(),
            ArrayField::new('ResearchGroups')
                ->hideOnIndex()
                ->setDisabled(),
            CollectionField::new('personDataRepositories')
                ->hideOnIndex()
                ->setDisabled(),
            CollectionField::new('Datasets')
                ->hideOnIndex()
                ->setDisabled(),
            CollectionField::new('Publications')
                ->hideOnIndex()
                ->setDisabled(),
        ];
    }
}

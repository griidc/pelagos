<?php

namespace App\Controller\Admin;

use App\Entity\Person;
use Collection;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @extends AbstractCrudController<Person>
 */
class PersonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('firstName'),
            TextField::new('lastName'),
            EmailField::new('emailAddress'),
            TelephoneField::new('phoneNumber'),
            TextareaField::new('deliveryPoint'),
            TextField::new('city'),
            TextField::new('administrativeArea'),
            TextField::new('postalCode'),
            TextField::new('country'),
            UrlField::new('url'),
            TextField::new('organization'),
            TextField::new('position'),
            CollectionField::new('personFundingOrganizations')
                ->setDisabled(),
            ArrayField::new('ResearchGroups')
                ->setDisabled(),
            CollectionField::new('personDataRepositories')
                ->setDisabled(),
            AssociationField::new('account')
                ->setDisabled(),
            CollectionField::new('Datasets')
                ->setDisabled(),
            CollectionField::new('Publications')
                ->setDisabled(),




        ];
    }
}

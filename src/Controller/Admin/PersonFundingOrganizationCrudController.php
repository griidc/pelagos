<?php

namespace App\Controller\Admin;

use App\Entity\PersonFundingOrganization;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Person Funding Organization CRUD Controller
 *
 * @extends AbstractCrudController<PersonFundingOrganization>
 */
class PersonFundingOrganizationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PersonFundingOrganization::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('person')->setRequired(true),
            AssociationField::new('fundingOrganization')->onlyWhenCreating(),
            AssociationField::new('role'),
            TextField::new('label'),
        ];
    }
}

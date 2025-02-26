<?php

namespace App\Controller\Admin;

use App\Entity\ResearchGroup;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ResearchGroupCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ResearchGroup::class;
    }


    /**
     * Configure fields for EZAdmin CRUD Controller.
     *
     * @param string $pageName default param for parent method (not used)
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name'),
            TextField::new('shortName'),
            AssociationField::new('fundingCycle'),
            TextField::new('FundingOrganization')->setDisabled(),
            TextField::new('url')->hideOnIndex(),
            TelephoneField::new('phoneNumber')->hideOnIndex(),
            UrlField::new('url')->hideOnIndex(),
            TextareaField::new('deliveryPoint')->hideOnIndex(),
            TextField::new('city')->hideOnIndex(),
            TextField::new('administrativeArea')->hideOnIndex(),
            TextField::new('postalCode')->hideOnIndex(),
            TextField::new('country')->hideOnIndex(),
            TextareaField::new('description')->hideOnIndex(),
            EmailField::new('emailAddress')->hideOnIndex(),
        ];
    }
}

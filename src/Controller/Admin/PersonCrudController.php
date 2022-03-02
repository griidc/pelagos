<?php

namespace App\Controller\Admin;

use App\Entity\Person;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;


class PersonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('firstName'),
            TextField::new('lastName'),
            TextField::new('emailAddress'),
            TextField::new('phoneNumber'),
            TextareaField::new('deliveryPoint'),
            TextField::new('city'),
            TextField::new('administrativeArea'),
            TextField::new('postalCode'),
            TextField::new('country'),


        ];
    }
}

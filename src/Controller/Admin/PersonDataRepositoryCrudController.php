<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\PersonDataRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * PersonDataRepositoryCrudController class.
 *
 * @extends AbstractCrudController<PersonDataRepository>
 */
#[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
class PersonDataRepositoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PersonDataRepository::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('person'),
            AssociationField::new('dataRepository')->onlyWhenCreating(),
            AssociationField::new('role'),
            TextField::new('label'),
        ];
    }
}

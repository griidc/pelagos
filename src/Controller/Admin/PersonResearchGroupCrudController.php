<?php

namespace App\Controller\Admin;

use App\Entity\Person;
use App\Entity\PersonResearchGroup;
use App\Entity\ResearchGroup;
use App\Entity\ResearchGroupRole;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PersonResearchGroupCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PersonResearchGroup::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
            ->onlyOnIndex(),
            AssociationField::new('person')
            ->setSortProperty('lastName'),
            AssociationField::new('researchGroup')
            ->setSortProperty('name'),
            ChoiceField::new('role')
            ->setChoices(ResearchGroupRole::ROLES)
            ->onlyOnForms(),
            TextField::new('label'),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // ->setDefaultSort(['person' => 'ASC', 'researchGroup' => 'ASC'])
            ;
    }
}

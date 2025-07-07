<?php

namespace App\Controller\Admin;

use App\Entity\Person;
use App\Entity\PersonResearchGroup;
use App\Entity\ResearchGroup;
use App\Entity\ResearchGroupRole;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<PersonResearchGroup>
 */
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
            AssociationField::new('person'),
            AssociationField::new('researchGroup')->onlyWhenCreating(),
            AssociationField::new('role'),
            TextField::new('label'),
        ];
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['person.lastName' => 'ASC']);
    }
}

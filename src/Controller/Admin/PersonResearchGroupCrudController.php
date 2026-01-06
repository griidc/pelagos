<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\Person;
use App\Entity\PersonResearchGroup;
use App\Entity\ResearchGroup;
use App\Entity\ResearchGroupRole;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @extends AbstractCrudController<PersonResearchGroup>
 */
#[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
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
            ->setQueryBuilder(
                function (QueryBuilder $queryBuilder) {
                    $queryBuilder->orderBy('entity.lastName', 'ASC');
                }
            )->setRequired(true)->setFormTypeOption('placeholder', 'Select a person'),
            AssociationField::new('researchGroup')->onlyWhenCreating(),
            AssociationField::new('role')->setRequired(true)->setFormTypeOption('placeholder', 'Select a role'),
            TextField::new('label')->setRequired(true)->setFormTypeOption('attr', ['placeholder' => 'Enter a label'])
        ];
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['person.lastName' => 'ASC']);
    }
}

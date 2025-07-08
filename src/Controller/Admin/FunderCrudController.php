<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\Entity;
use App\Entity\Funder;
use App\Entity\Dataset;
use App\Repository\DatasetRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Funder Crud Controller.
 *
 *  @extends AbstractCrudController<Funder>
 */
#[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
class FunderCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Returns Entity Class Name.
     */
    public static function getEntityFqcn(): string
    {
        return Funder::class;
    }

    /**
     * Configure fields for EZAdmin CRUD Controller.
     *
     * @param string $pageName default param for parent method (not used)
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
            ->onlyOnIndex(),
            TextField::new('name'),
            TextField::new('shortName'),
            TextField::new('referenceUri'),
            ChoiceField::new('source')->setChoices(Funder::SOURCES),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('source')->setChoices(Funder::SOURCES))
        ;
    }

    #[\Override]
    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /* @var Funder $entityInstance */
        $entityInstance->setSource(Funder::SOURCE_DRPM);
        $entityInstance->setModifier($this->getUser()->getPerson());
        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * Configure the Crud actions.
     *
     * @param Actions $actions actions object that need to be configured
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fa fa-plus-circle')
                    ->setLabel('Create New Funder');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Delete')
                    ->displayIf(function (Funder $funder) {
                        return !$this->isFunderBeingUsed($funder);
                    });
            });
    }

    /**
     * CRUD configuration function.
     *
     * @param Crud $crud instance for crud controller to add additional configuration
     */
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Funders')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Funder')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Funder')
            ->showEntityActionsInlined();
    }

    /**
     * Is this funder linked with any dataset.
     */
    private function isFunderBeingUsed(Funder $funder): bool
    {
        $datasetRepository = $this->entityManager->getRepository(Dataset::class);

        return count($datasetRepository->findByFunder($funder)) > 0;
    }
}

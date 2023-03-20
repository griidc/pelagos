<?php

namespace App\Controller\Admin;

use App\Entity\Entity;
use App\Entity\Funder;
use App\Entity\Dataset;
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

/**
 * Funder Crud Controller.
 */
class FunderCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

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

    /**
     * Overwrite for when entity is created.
     *
     * @param string $entityFqcn entity class name
     */
    public function createEntity(string $entityFqcn): Entity
    {
        $funder = new $entityFqcn();
        $funder->setCreator($this->getUser()->getPerson());

        return $funder;
    }

    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        /* @var Funder $entityInstance */
        $entityInstance->setSource(Funder::SOURCE_DRPM);
        $entityInstance->setModifier($this->getUser()->getPerson());
        $entityManager->persist($entityInstance);
        $entityManager->flush();
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
        $entityManager = $this->container->get('doctrine')->getManager();

        /** @var DatasetRepository $datasetRepository */
        $datasetRepository = $entityManager->getRepository(Dataset::class);

        return count($datasetRepository->findByFunder($funder)) > 0;
    }
}

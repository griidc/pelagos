<?php

namespace App\Controller\Admin;

use App\Entity\Entity;
use App\Entity\Funder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

/**
 * Funder Crud Controller.
 */
class FunderCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

    /**
     * Returns Entity Class Name.
     *
     * @return string
     */
    public static function getEntityFqcn(): string
    {
        return Funder::class;
    }

     /**
      * Configure fields for EZAdmin CRUD Controller.
      *
      * @param string $pageName Default param for parent method (not used).
      *
      * @return iterable
      */
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
            ->onlyOnIndex(),
            TextField::new('name'),
            TextField::new('referenceUri'),
            ChoiceField::new('source')->setChoices(Funder::SOURCES)->hideWhenCreating(),
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
     * @param string $entityFqcn Entity class name.
     *
     * @return Entity
     */
    public function createEntity(string $entityFqcn): Entity
    {
        $funder = new $entityFqcn();
        $funder->setCreator($this->getUser()->getPerson());

        return $funder;
    }

     /**
     * Configure the Crud actions.
     *
     * @param Actions $actions Actions object that need to be configured.
     *
     * @return Actions
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
                    ->setLabel('Delete');
            });
    }

    /**
     * CRUD configuration function.
     *
     * @param Crud $crud Instance for crud controller to add additional configuration.
     *
     * @return Crud
     */
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Funders')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Funder')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Funder')
            ->showEntityActionsInlined();
    }
}

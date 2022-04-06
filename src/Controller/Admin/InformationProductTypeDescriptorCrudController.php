<?php

namespace App\Controller\Admin;

use App\Entity\InformationProductTypeDescriptor;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Information Product Type Descriptor controller.
 */
class InformationProductTypeDescriptorCrudController extends AbstractCrudController
{
    /**
     * Returns Fully Qualified Class Name.
     *
     * @return string
     */
    public static function getEntityFqcn(): string
    {
        return InformationProductTypeDescriptor::class;
    }

    /**
     * Configure Crud Actions.
     *
     * @param Actions $actions
     *
     * @return Actions
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fa fa-plus-circle')
                    ->setLabel('Create New Information Product Type Descriptor')
                    ;
            });
    }

    /**
     * CRUD configuration function.
     *
     * @param Crud $crud
     *
     * @return Crud
     */
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Information Product Descriptor')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Information Product Descriptor')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Information Product Descriptor')
            ->showEntityActionsInlined()
            ;
    }

    /**
     * Configure fields for CRUD.
     *
     * @param string $pageName
     *
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
            ->onlyOnIndex(),
            TextField::new('description'),
        ];
    }

    /**
     * Overwrite for when entity is created.
     *
     * @param string $entityFqcn
     *
     * @return void
     */
    public function createEntity(string $entityFqcn)
    {
        $informationProductTypeDescriptor = new InformationProductTypeDescriptor();
        $informationProductTypeDescriptor->setCreator($this->getUser()->getPerson());

        return $informationProductTypeDescriptor;
    }

    /**
     * Update the Crud entity.
     *
     * @param EntityManagerInterface $entityManager  The Entity Manager.
     * @param mixed                  $entityInstance The entity to update.
     *
     * @return void
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var InformationProductTypeDescriptor $entityInstance */
        $entityInstance->setModifier($this->getUser()->getPerson());
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }
}

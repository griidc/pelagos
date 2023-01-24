<?php

namespace App\Controller\Admin;

use App\Entity\InformationProduct;
use App\Entity\ProductTypeDescriptor;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Information Product Type Descriptor controller.
 */
class ProductTypeDescriptorCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

    /**
     * Returns Fully Qualified Class Name.
     */
    public static function getEntityFqcn(): string
    {
        return ProductTypeDescriptor::class;
    }

    /**
     * Configure Crud Actions.
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fa fa-plus-circle')
                    ->setLabel('Create New Product Type Descriptor');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Delete')
                    ->displayIf(function (ProductTypeDescriptor $productTypeDescriptor) {
                        return !$this->isProductTypeInUse($productTypeDescriptor);
                    });
            });
    }

    /**
     * CRUD configuration function.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Product Type Descriptors')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Product Type Descriptor')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Product Type Descriptor')
            ->showEntityActionsInlined()
        ;
    }

    /**
     * Configure fields for CRUD.
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
     * @return void
     */
    public function createEntity(string $entityFqcn)
    {
        $productTypeDescriptor = new ProductTypeDescriptor();
        $productTypeDescriptor->setCreator($this->getUser()->getPerson());

        return $productTypeDescriptor;
    }

    /**
     * Is this Product Type Descriptor in use on an Information Product.
     */
    private function isProductTypeInUse(ProductTypeDescriptor $productTypeDescriptor): bool
    {
        $entityManager = $this->getDoctrine()->getManager();

        /** @var InformationProductRepository $informationProductRepository */
        $informationProductRepository = $entityManager->getRepository(InformationProduct::class);

        return count($informationProductRepository->findByProductTypeDescriptor($productTypeDescriptor)) > 0;
    }
}

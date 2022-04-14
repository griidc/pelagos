<?php

namespace App\Controller\Admin;

use App\Entity\InformationProduct;
use App\Entity\ProductTypeDescriptor;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Information Product Type Descriptor controller.
 */
class ProductTypeDescriptorCrudController extends AbstractCrudController
{
    /**
     * Returns Fully Qualified Class Name.
     *
     * @return string
     */
    public static function getEntityFqcn(): string
    {
        return ProductTypeDescriptor::class;
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
     *
     * @param Crud $crud
     *
     * @return Crud
     */
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Information Product Type Descriptor')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Information Product Type Descriptor')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Information Product Type Descriptor')
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
        $productTypeDescriptor = new ProductTypeDescriptor();
        $productTypeDescriptor->setCreator($this->getUser()->getPerson());

        return $productTypeDescriptor;
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
        /** @var ProductTypeDescriptor $entityInstance */
        $entityInstance->setModifier($this->getUser()->getPerson());
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    /**
     * Crud delete an entity.
     *
     * @param EntityManagerInterface $entityManager  The Entity Manager.
     * @param mixed                  $entityInstance The entity to delete.
     *
     * @return void
     */
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        try {
            $entityManager->remove($entityInstance);
        } catch (\Exception $e) {
            throw new AccessDeniedHttpException('Unable to delete. Reason:' . $e->getMessage());
        }

        $entityManager->flush();
    }

    /**
     * Is this Product Type Descriptor in use on an Information Product.
     *
     * @param ProductTypeDescriptor $productTypeDescriptor
     *
     * @return boolean
     */
    private function isProductTypeInUse(ProductTypeDescriptor $productTypeDescriptor): bool
    {
        $entityManager = $this->getDoctrine()->getManager();

        /** @var InformationProductRepository $informationProductRepository */
        $informationProductRepository = $entityManager->getRepository(InformationProduct::class);

        return count($informationProductRepository->findByProductTypeDescriptor($productTypeDescriptor)) > 0;
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\DigitalResourceTypeDescriptor;
use App\Entity\InformationProduct;
use App\Repository\InformationProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Digital Resource Type Crud Controller.
 */
class DigitalResourceTypeDescriptorCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

    /**
     * Returns Fully Qualified Class Name.
     */
    public static function getEntityFqcn(): string
    {
        return DigitalResourceTypeDescriptor::class;
    }

    /**
     * Configure the Crud actions.
     */
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action
                    ->setIcon('fa fa-plus-circle')
                    ->setLabel('Create New Digital Resource Type Descriptor');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Delete')
                    ->displayIf(function (DigitalResourceTypeDescriptor $digitalResourceTypeDescriptor) {
                        return !$this->isDigitalResourceTypeInUse($digitalResourceTypeDescriptor);
                    });
            });
    }

    /**
     * CRUD configuration function.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Digital Resource Type Descriptors')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Digital Resource Type Descriptor')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Digital Resource Type Descriptor')
            ->showEntityActionsInlined();
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
        $digitalResourceTypeDescriptor = new DigitalResourceTypeDescriptor();
        $digitalResourceTypeDescriptor->setCreator($this->getUser()->getPerson());

        return $digitalResourceTypeDescriptor;
    }

    /**
     * Is this digital resource in use on an Information Product.
     */
    private function isDigitalResourceTypeInUse(DigitalResourceTypeDescriptor $digitalResourceTypeDescriptor, EntityManagerInterface $entityManager): bool
    {
        /** @var InformationProductRepository $informationProductRepository */
        $informationProductRepository = $entityManager->getRepository(InformationProduct::class);

        return count($informationProductRepository->findByDigitalResourceTypeDescriptor($digitalResourceTypeDescriptor)) > 0;
    }
}

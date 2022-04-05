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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Undocumented class
 */
class DigitalResourceTypeDescriptorCrudController extends AbstractCrudController
{
    /**
     * Returns Fully Qualified Class Name.
     *
     * @return string
     */
    public static function getEntityFqcn(): string
    {
        return DigitalResourceTypeDescriptor::class;
    }

    /**
     * Undocumented function
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
                    ->setLabel('Create New Digital Resource Type Descriptor')
                    ;
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Delete')
                    ->displayIf(function (DigitalResourceTypeDescriptor $digitalResourceTypeDescriptor){
                        return !$this->isDigitalResourceTypeInUse($digitalResourceTypeDescriptor);
                    })
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
            ->setPageTitle(Crud::PAGE_INDEX, 'Digital Resource Type Descriptors')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Digital Resource Type Descriptor')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Digital Resource Type Descriptor')
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
        $digitalResourceTypeDescriptor = new DigitalResourceTypeDescriptor();
        $digitalResourceTypeDescriptor->setCreator($this->getUser()->getPerson());

        return $digitalResourceTypeDescriptor;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var DigitalResourceTypeDescriptor $entityInstance */
        $entityInstance->setModifier($this->getUser()->getPerson());
        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Check first if this is not being used!
        try {
            $entityManager->remove($entityInstance);
        } catch (\Exception $e) {
            throw new AccessDeniedHttpException('Unable to delete. Reason:' . $e->getMessage());
        }

        $entityManager->flush();
    }

    private function isDigitalResourceTypeInUse(DigitalResourceTypeDescriptor $digitalResourceTypeDescriptor): bool
    {
        $entityManager = $this->getDoctrine()->getManager();

        /** @var InformationProductRepository $informationProductRepository */
        $informationProductRepository = $entityManager->getRepository(InformationProduct::class);

        return count($informationProductRepository->findByDigitalResourceTypeDescriptor($digitalResourceTypeDescriptor)) > 0;
    }

}

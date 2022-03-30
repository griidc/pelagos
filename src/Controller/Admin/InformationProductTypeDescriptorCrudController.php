<?php

namespace App\Controller\Admin;

use App\Entity\InformationProductTypeDescriptor;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class InformationProductTypeDescriptorCrudController extends AbstractCrudController
{

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Information Product Descriptor');
    }

    public static function getEntityFqcn(): string
    {
        return InformationProductTypeDescriptor::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
            ->onlyOnIndex(),
            TextField::new('description'),
        ];
    }

    public function createEntity(string $entityFqcn)
    {
        $informationProductTypeDescriptor = new InformationProductTypeDescriptor();
        $informationProductTypeDescriptor->setCreator($this->getUser()->getPerson());

        return $informationProductTypeDescriptor;
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\DataRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * DataRepositoryCrudController class.
 *
 * @extends AbstractCrudController<DataRepository>
 */
class DataRepositoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DataRepository::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action
                    ->setIcon('fa fa-eye')
                    ->setLabel('View');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fa fa-edit')
                    ->setLabel('Edit');
            })
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural('Data Repositories')
            ->setEntityLabelInSingular('Data Repository')
            ->setPageTitle(Crud::PAGE_INDEX, 'Data Repository List')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Data Repository')
            ->setPageTitle(Crud::PAGE_NEW, 'Add Data Repository')
            ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name'),
            EmailField::new('emailAddress'),
            TextareaField::new('description'),
            UrlField::new('url'),
            TelephoneField::new('phoneNumber'),
            TextareaField::new('deliveryPoint'),
            TextField::new('city'),
            TextField::new('administrativeArea')->setLabel('state'),
            TextField::new('postalCode'),
            TextField::new('country'),
            CollectionField::new('personDataRepositories')
                ->useEntryCrudForm()


        ];
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\DataRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * DataRepositoryCrudController class.
 *
 * @extends AbstractCrudController<DataRepository>
 */
#[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
class DataRepositoryCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return DataRepository::class;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
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

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['modificationTimeStamp' => 'DESC'])
            ->setEntityLabelInPlural('Data Repositories')
            ->setEntityLabelInSingular('Data Repository')
            ->setPageTitle(Crud::PAGE_INDEX, 'Data Repository List')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Data Repository')
            ->setPageTitle(Crud::PAGE_NEW, 'Add Data Repository')
            ->showEntityActionsInlined()
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('name'),
            EmailField::new('emailAddress')->setLabel('Email'),
            TextareaField::new('description'),
            UrlField::new('url'),
            TelephoneField::new('phoneNumber'),
            TextareaField::new('deliveryPoint'),
            TextField::new('city'),
            TextField::new('administrativeArea')->setLabel('State'),
            TextField::new('postalCode'),
            TextField::new('country'),
            CollectionField::new('personDataRepositories')->useEntryCrudForm(),
            DateField::new('creationTimeStamp')->setLabel('Created At')
                ->onlyOnDetail()
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
            AssociationField::new('creator')->setLabel('Created By')
                ->onlyOnDetail()
                ->setTemplateName('crud/field/generic'),
            DateField::new('modificationTimeStamp')->setLabel('Last Modified At')
                ->onlyOnDetail()
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
            AssociationField::new('modifier')->setLabel('Last Modified By')
                ->onlyOnDetail()
                ->setTemplateName('crud/field/generic'),
        ];
    }
}

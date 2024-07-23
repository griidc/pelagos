<?php

namespace App\Controller\Admin;

use App\Entity\LogActionItem;
use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LogActionItemCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LogActionItem::class;
    }

    /**
     * Configure the Crud actions.
     *
     * @param Actions $actions actions object that need to be configured
     */
    public function configureActions(Actions $actions): Actions
    {
        $actions->disable(Action::NEW);
        $actions->disable(Action::EDIT);
        $actions->disable(Action::DELETE);

        return $actions;
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
            TextField::new('actionName', 'What Happened'),
            TextField::new('subjectEntityName', 'Happened To'),
            DateTimeField::new('creationTimeStamp', 'Happened When'),
        ];
    }

    /**
     * CRUD configuration function.
     *
     * @param Crud $crud instance for crud controller to add additional configuration
     */
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setPageTitle(Crud::PAGE_INDEX, 'Logged Actions')
            ->setDefaultSort(['creationTimeStamp' => 'DESC'])
            ->showEntityActionsInlined();
    }
}

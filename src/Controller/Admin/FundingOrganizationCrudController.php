<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\FundingCycle;
use App\Entity\FundingOrganization;
use App\Form\PersonFundingOrganizationType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Funding Org Easy Admin controller.
 *
 * @extends AbstractCrudController<FundingOrganization>
 */
#[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
class FundingOrganizationCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Returns Fully Qualified Class Name.
     */
    public static function getEntityFqcn(): string
    {
        return FundingOrganization::class;
    }

    /**
     * Configure Crud Actions.
     */
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('dataRepository')->onlyOnForms(),
            AssociationField::new('defaultFunder')->onlyOnForms(),
            TextField::new('name'),
            TextField::new('description')->onlyOnForms(),
            TextField::new('shortName'),
            EmailField::new('emailAddress')->onlyOnForms(),
            TextField::new('url')->onlyOnForms(),
            TextField::new('phoneNumber')->onlyOnForms(),
            TextField::new('deliveryPoint')->onlyOnForms(),
            TextField::new('city')->onlyOnForms(),
            TextField::new('administrativeArea')->onlyOnForms(),
            TextField::new('postalCode')->onlyOnForms(),
            TextField::new('country')->onlyOnForms(),
            NumberField::new('sortOrder')->onlyOnForms(),
            CollectionField::new('personFundingOrganizations')
                ->onlyOnForms()
                ->useEntryCrudForm(),
            CollectionField::new('fundingCycles')
                ->setDisabled()
                ->hideOnIndex(),
        ];
    }

    /**
     * CRUD configuration function.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInSingular('Funding Organization')
            ->setEntityLabelInPlural('Funding Organizations')
            ->setPageTitle(Crud::PAGE_INDEX, 'Funding Organizations')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Funding Organization')
            ->setPageTitle(Crud::PAGE_NEW, 'Create Funding Organization')
            ->showEntityActionsInlined();
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
                    ->setLabel('Create Funding Organization');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Delete')
                    ->displayIf(function (FundingOrganization $fundingOrganization) {
                        return !$this->isFundingOrgInUse($fundingOrganization);
                    });
            })
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
            });
    }

    /**
     * Is this Funding organization in use funding cycle.
     */
    private function isFundingOrgInUse(FundingOrganization $fundingOrganization): bool
    {
        return $this->entityManager->getRepository(FundingCycle::class)->count(['fundingOrganization' => $fundingOrganization]) > 0;
    }
}

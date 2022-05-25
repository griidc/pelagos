<?php

namespace App\Controller\Admin;

use App\Entity\FundingCycle;
use App\Entity\FundingOrganization;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Funding Org Easy Admin controller.
 */
class FundingOrganizationCrudController extends AbstractCrudController
{
    /**
     * Returns Fully Qualified Class Name.
     *
     * @return string
     */
    public static function getEntityFqcn(): string
    {
        return FundingOrganization::class;
    }

    /**
     * Configure Crud Actions.
     *
     * @param string $pageName
     *
     * @return iterable
     */
    public function configureFields(string $pageName): iterable
    {
        $fields = array();
        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT])) {
            $fields[] = AssociationField::new('dataRepository');
        }

        return array_merge($fields, [
            TextField::new('name'),
            TextField::new('description')->onlyOnForms(),
            TextField::new('shortName'),
            EmailField::new('emailAddress'),
            TextField::new('url'),
            TextField::new('phoneNumber'),
            TextField::new('deliveryPoint'),
            TextField::new('city'),
            TextField::new('administrativeArea'),
            TextField::new('postalCode'),
            TextField::new('country'),
            NumberField::new('sortOrder'),
        ]);
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
            ->disable(Action::DELETE);
    }

    /**
     * Is this Funding organization in use funding cycle.
     *
     * @param FundingOrganization $fundingOrganization
     *
     * @return boolean
     */
    private function isFundingOrgInUse(FundingOrganization $fundingOrganization): bool
    {
        $entityManager = $this->getDoctrine()->getManager();

        $fundingCycles = $entityManager->getRepository(FundingCycle::class)->findBy(['fundingOrganization'=> $fundingOrganization]);

        return count($fundingCycles) > 0;
    }
}

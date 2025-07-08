<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\DatasetSubmission;
use App\Entity\DistributionPoint;
use App\Entity\NationalDataCenter;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @extends AbstractCrudController<NationalDataCenter>
 */
#[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
class NationalDataCenterCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

    /**
     * Class constructor, for EntityManager injection.
     */
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public static function getEntityFqcn(): string
    {
        return NationalDataCenter::class;
    }

    /**
     * CRUD configuration function.
     */
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural('National Data Centers')
            ->setEntityLabelInSingular('National Data Center')
            ->setPageTitle(Crud::PAGE_INDEX, 'National Data Center List')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit National Data Center')
            ->setPageTitle(Crud::PAGE_NEW, 'Add National Data Center')
            ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('organizationName')->setLabel('Name'),
            UrlField::new('organizationURL')->setLabel('URL'),
            TelephoneField::new('phoneNumber'),
            TextField::new('deliveryPoint')->setLabel('Address'),
            TextField::new('city'),
            TextField::new('administrativeArea')->setLabel('State/Province')
                ->setHelp('State or Province, if applicable. Use 2-letter code for US states.'),
            TextField::new('postalCode'),
            TextField::new('country'),
            EmailField::new('emailAddress')->setLabel('email'),
        ];
    }

    /**
     * Configure Crud Actions.
     */
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
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->displayIf(function (NationalDataCenter $nationalDataCenter) {
                        return !$this->isNationalDataCenterInUse($nationalDataCenter);
                    });
            })
        ;
    }

    /**
     * Check to see if entity is in use.
     */
    private function isNationalDataCenterInUse(NationalDataCenter $nationalDataCenter): bool
    {
        return $this->entityManager->getRepository(DistributionPoint::class)->count(['dataCenter' => $nationalDataCenter]) > 0;
    }
}

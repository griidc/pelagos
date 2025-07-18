<?php

namespace App\Controller\Admin;

use App\Entity\Account;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
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

    #[\Override]
    public static function getEntityFqcn(): string
    {
        return NationalDataCenter::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['modificationTimeStamp' => 'DESC'])
            ->setEntityLabelInPlural('National Data Centers')
            ->setEntityLabelInSingular('National Data Center')
            ->setPageTitle(Crud::PAGE_INDEX, 'National Data Center')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit National Data Center')
            ->setPageTitle(Crud::PAGE_NEW, 'Add National Data Center')
            ->showEntityActionsInlined()
        ;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('organizationName')->setLabel('Name'),
            TextField::new('organizationURL')->setLabel('URL'),
            TextField::new('phoneNumber')->hideOnIndex(),
            TextareaField::new('deliveryPoint')->setLabel('Address')->hideOnIndex(),
            TextField::new('city'),
            TextField::new('administrativeArea')->setLabel('State'),
            TextField::new('postalCode')->hideOnIndex(),
            TextField::new('country'),
            TextField::new('emailAddress')->setLabel('Email'),
            DateField::new('creationTimeStamp')->setLabel('Created At')
                ->onlyOnDetail()
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
            TextField::new('creator')->setLabel('Created By')
                ->onlyOnDetail(),
            DateField::new('modificationTimeStamp')->setLabel('Last Modified At')
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz')
                ->setDisabled(),
            TextField::new('modifier')->setLabel('Last Modified By')
                ->onlyOnDetail(),
        ];
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
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash')
                    ->displayIf(function (NationalDataCenter $nationalDataCenter) {
                        return !$this->isNationalDataCenterInUse($nationalDataCenter);
                    });
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
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

<?php

namespace App\Controller\Admin;

use App\Entity\Account;
use App\Entity\Funder;
use App\Entity\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Funder Crud Controller.
 *
 *  @extends AbstractCrudController<Funder>
 */
#[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
class FunderCrudController extends AbstractCrudController
{
    use EasyAdminCrudTrait;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }


    #[\Override]
    public static function getEntityFqcn(): string
    {
        return Funder::class;
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
            ->onlyOnIndex(),
            TextField::new('name'),
            TextField::new('shortName'),
            TextField::new('referenceUri'),
            ChoiceField::new('source')->setChoices(Funder::SOURCES),
            DateField::new('creationTimeStamp')->setLabel('Created At')
                ->onlyOnDetail()
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
            TextField::new('creator')->setLabel('Created By')
                ->onlyOnDetail(),
            DateField::new('modificationTimeStamp')->setLabel('Last Modified At')
                ->onlyOnDetail()
                ->setFormat('yyyy-MM-dd HH:mm:ss zzz'),
            TextField::new('modifier')->setLabel('Last Modified By')
                ->onlyOnDetail(),
        ];
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('source')->setChoices(Funder::SOURCES))
        ;
    }

    #[\Override]
    public function updateEntity(EntityManagerInterface $entityManager, mixed $entityInstance): void
    {
        $entityInstance->setSource(Funder::SOURCE_DRPM);
        /** @var Account $account */
        $account = $this->getUser();
        $entityInstance->setModifier($account->getPerson());
        parent::updateEntity($entityManager, $entityInstance);
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $actions
        ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
        ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
            return $action
                ->setIcon('fa fa-plus-circle')
                ->setLabel('Create New Funder');
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
        })
        ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
            return $action
                ->setIcon('fa fa-trash')
                ->setLabel('Delete')
                ->displayIf(function (Funder $funder) {
                    return !$this->isFunderBeingUsed($funder);
                });
        })
        ->update(Crud::PAGE_DETAIL, Action::DELETE, function (Action $action) {
            return $action
                ->setIcon('fa fa-trash')
                ->setLabel('Delete')
                ->displayIf(function (Funder $funder) {
                    return !$this->isFunderBeingUsed($funder);
                });
        })
        ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
            return $action
                ->setIcon('fa fa-save')
                ->setLabel('Save and Close');
        });
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
         return parent::configureCrud($crud)
            ->setDefaultSort(['modificationTimeStamp' => 'DESC'])
            ->setEntityLabelInPlural('Funders')
            ->setEntityLabelInSingular('Funder')
            ->setPageTitle(Crud::PAGE_INDEX, 'Funder List')
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit Funder')
            ->setPageTitle(Crud::PAGE_NEW, 'Add Funder')
            ->showEntityActionsInlined()
        ;
    }

    /**
     * Is this funder linked with any dataset.
     */
    private function isFunderBeingUsed(Funder $funder): bool
    {
        $datasetRepository = $this->entityManager->getRepository(Dataset::class);

        return count($datasetRepository->findByFunder($funder)) > 0;
    }
}

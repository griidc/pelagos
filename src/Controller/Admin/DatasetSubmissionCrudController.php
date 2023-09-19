<?php

namespace App\Controller\Admin;

use App\Entity\DatasetSubmission;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;

class DatasetSubmissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DatasetSubmission::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = $this->container->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters);
        // $queryBuilder->where('entity.sequence = ' . DatasetSubmission::STATUS_COMPLETE);

        return $queryBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setDisabled(),
            IntegerField::new('sequence')->setDisabled(),
            IntegerField::new('status')->formatValue(function(int $status) {
                return match($status) {
                    DatasetSubmission::STATUS_UNSUBMITTED => 'Unsubmitted',
                    DatasetSubmission::STATUS_INCOMPLETE => 'Incomplete',
                    DatasetSubmission::STATUS_IN_REVIEW => 'In Review',
                    DatasetSubmission::STATUS_COMPLETE => 'Complete',
                    default => 'Unknown'
                };
            })->setDisabled(),
            AssociationField::new('dataset')->renderAsNativeWidget()->setDisabled(),
            AssociationField::new('keywords'),
            TextareaField::new('title'),
            TextareaField::new('abstract')->hideOnIndex(),
            TextareaField::new('purpose')->hideOnIndex(),
            TextareaField::new('suppParams')->hideOnIndex(),
            TextareaField::new('suppMethods')->hideOnIndex(),
            TextareaField::new('suppInstruments')->hideOnIndex(),
            TextareaField::new('suppSampScalesRates')->hideOnIndex(),
            TextareaField::new('suppErrorAnalysis')->hideOnIndex(),
            TextareaField::new('suppProvenance')->hideOnIndex(),
            ArrayField::new('themeKeywords')->hideOnIndex(),
            ArrayField::new('placeKeywords')->hideOnIndex(),
            ArrayField::new('topicKeywords')->hideOnIndex(),
        ];
    }
}

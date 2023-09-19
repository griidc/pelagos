<?php

namespace App\Controller\Admin;

use App\Entity\DatasetSubmission;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DatasetSubmissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DatasetSubmission::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setDisabled(),
            IntegerField::new('sequence'),
            IntegerField::new('status')->formatValue(function(int $status) {
                return match($status) {
                    DatasetSubmission::STATUS_UNSUBMITTED => 'Unsubmitted',
                    DatasetSubmission::STATUS_INCOMPLETE => 'Incomplete',
                    DatasetSubmission::STATUS_IN_REVIEW => 'In Review',
                    DatasetSubmission::STATUS_COMPLETE => 'Complete',
                    default => 'Unknown'
                };
            }),
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

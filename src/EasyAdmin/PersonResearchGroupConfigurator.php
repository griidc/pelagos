<?php

namespace App\EasyAdmin;

use App\Entity\Person;
use App\Entity\PersonResearchGroup;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use function Symfony\Component\String\u;

class PersonResearchGroupConfigurator implements FieldConfiguratorInterface
{
    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {

        $fieldFqcn = $field->getFieldFqcn();
        $entityFqcn = $entityDto->getFqcn();

        return ($fieldFqcn === AssociationField::class and $entityFqcn === PersonResearchGroup::class);

    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {

        // $crud = $context->getCrud();

        // dd($field, $entityDto, $context, $crud);




        // $crud = $context->getCrud();
        // if ($crud?->getCurrentPage() === Crud::PAGE_DETAIL) {
        //     return;
        // }
        // if (strlen($field->getFormattedValue()) <= self::MAX_LENGTH) {
        //     return;
        // }

        // $truncatedValue = u($field->getFormattedValue())
        //     ->truncate(self::MAX_LENGTH, '...', false);
        // $field->setFormattedValue($truncatedValue);
    }
}

<?php

namespace App\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\TextFilterType;

final class ResearchGroupFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, string $label = 'Research Group'): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(TextFilterType::class)
            ->setFormTypeOption('translation_domain', 'EasyAdminBundle');
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $comparison = $filterDataDto->getComparison();
        // $parameterName = $filterDataDto->getParameterName();
        $value = $filterDataDto->getValue();

        $queryBuilder->join('entity.dataset', 'ds');
        $queryBuilder->join('ds.researchGroup', 'rg');

        $queryBuilder->andWhere(sprintf('rg.name %s :value', $comparison))
            ->setParameter('value', $value);
    }
}

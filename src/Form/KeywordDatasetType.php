<?php

namespace App\Form;

use App\Entity\Keyword;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KeywordDatasetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('keywords', CollectionType::class, [
            'label' => 'Keywords',
            'entry_type' => EntityType::class,
            'entry_options' => [
                'class' => Keyword::class,
            ],
            'by_reference' => true,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'required' => false,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\InformationProduct;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InformationProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('creators')
            ->add('publisher')
            ->add('externalDoi')
            ->add('published')
            ->add('remoteResource')
            ->add('creationTimeStamp')
            ->add('modificationTimeStamp')
            ->add('researchGroups')
            ->add('creator')
            ->add('modifier')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InformationProduct::class,
        ]);
    }
}

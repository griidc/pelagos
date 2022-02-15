<?php

namespace App\Form;

use App\Entity\InformationProduct;

use App\Entity\ResearchGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('file')
            // ->add('creationTimeStamp')
            // ->add('modificationTimeStamp')
            // ->add('creator')
            // ->add('modifier')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InformationProduct::class,
            'allow_extra_fields' => true
        ]);
    }
}

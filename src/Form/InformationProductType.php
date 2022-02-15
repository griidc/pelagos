<?php

namespace App\Form;

use App\Entity\InformationProduct;

use App\Entity\File;
use App\Entity\ResearchGroup;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
            ->add('file', EntityType::class, [
                // looks for choices from this entity
                'class' => File::class,

                // uses the User.username property as the visible option string
                'choice_label' => 'id',

                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
            ]);
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

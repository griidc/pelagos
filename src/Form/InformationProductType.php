<?php

namespace App\Form;

use App\Entity\File;
use App\Entity\InformationProduct;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InformationProductType extends AbstractType
{
    /**
     * @return void
     */
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
                'class' => File::class,
                'choice_label' => 'id',
            ])
            ->add('remoteUri')
            ;
    }

    /**
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InformationProduct::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}

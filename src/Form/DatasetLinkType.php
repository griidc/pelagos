<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\DatasetLinks;

class DatasetLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', Type\TextType::class, array(
                'label' => 'Link Url:',
                'required' => false,
            ))
            ->add('name', Type\TextType::class, array(
                'label' => 'Link Name:',
                'required' => false,
            ))
            ->add('description', Type\TextType::class, array(
                'label' => 'Link Description:',
                'required' => false,
            ))
            ->add('functionCode', Type\ChoiceType::class, array(
                'label' => 'Role:',
                'required' => false,
                'choices' => DatasetLinks::getFunctionCodeChoices(),
                'empty_data' => 'functionCode',
                'expanded' => false,
                'preferred_choices' => function ($role, $value) {
                    return $value === 'Function Code';
                },
            ))
            ->add('protocol', Type\TextType::class, array(
                'label' => 'Link Protocol:',
                'required' => false,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DatasetLinks::class,
        ]);
    }
}

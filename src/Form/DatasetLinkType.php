<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\DatasetLink;

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
                'label' => 'Link Function Code:',
                'required' => false,
                'choices' => DatasetLink::getFunctionCodeChoices(),
                'empty_data' => 'download',
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
            'data_class' => DatasetLink::class,
        ]);
    }
}

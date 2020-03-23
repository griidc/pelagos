<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\DatasetLink;

/**
 * A form for creating Dataset Links.
 */
class DatasetLinkType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('url', Type\TextType::class, array(
                'label' => 'Link Url:',
                'required' => false,
				'attr'  => array('class' => 'dataLinkUrl'),
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
                'expanded' => false,
                'preferred_choices' => function ($role, $value) {
                    return $value === 'Function Code';
                },
            ))
            ->add('protocol', Type\TextType::class, array(
                'label' => 'Link Protocol:',
                'required' => false,
            ));
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DatasetLink::class,
        ]);
    }
}

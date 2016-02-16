<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Pelagos\Entity\Person;

/**
 * A form for creating people.
 */
class PersonType extends AbstractType
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
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('emailAddress', TextType::class)
            ->add('phoneNumber', TextType::class, array('required' => false))
            ->add('deliveryPoint', TextType::class, array('required' => false))
            ->add('city', TextType::class, array('required' => false))
            ->add('administrativeArea', TextType::class, array('required' => false))
            ->add('postalCode', TextType::class, array('required' => false))
            ->add('country', TextType::class, array('required' => false))
            ->add('url', TextType::class, array('required' => false))
            ->add('organization', TextType::class, array('required' => false))
            ->add('position', TextType::class, array('required' => false));
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
        $resolver->setDefaults(array(
            'data_class' => Person::class,
            'allow_extra_fields' => true,
        ));
    }
}

<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Form class for data center.
 */
class DataCenterType extends AbstractType
{
    /**
     * Builds the form for Data center.
     *
     * @param FormBuilderInterface $builder An instance of FormBuilderInterface.
     * @param array                $options The options provided.
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organizationName', TextType::class, array(
                'label' => 'Organization Name:',
                'required' => true,
            ))
            ->add('organizationUrl', TextType::class, array(
                'label' => 'Organization URL:',
                'required' => true,
            ))
            ->add('phoneNumber', TextType::class, array(
                'label' => 'Phone Number:',
                'required' => false,
            ))
            ->add('deliveryPoint', TextareaType::class, array(
                'attr' => array('rows' => 3, 'maxlength' => 300),
                'label' => 'Delivery Point:',
                'required' => false,
            ))
            ->add('city', TextType::class, array(
                'label' => 'City:',
                'required' => false,
            ))
            ->add('administrativeArea', TextType::class, array(
                'label' => 'State/Province:',
                'required' => false,
            ))
            ->add('postalCode', TextType::class, array(
                'label' => 'Postal Code:',
                'required' => false,
            ))
            ->add('country', TextType::class, array(
                'label' => 'Country:',
                'required' => false,
            ))
            ->add('emailAddress', TextType::class, array(
                'label' => 'Email address:',
                'required' => false,
            ));
    }
}

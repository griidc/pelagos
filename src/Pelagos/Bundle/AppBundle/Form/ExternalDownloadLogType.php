<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * A form to end the dataset submission review.
 */
class ExternalDownloadLogType extends abstractType
{
    /**
     * Method to build a symfony form.
     *
     * @param FormBuilderInterface $builder The Symfony form builder.
     * @param array                $options The options to pass in.
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('udi', TextType::class, array(
                'label' => 'Enter the dataset UDI',
                'required' => true
            ))
            ->add('userType', ChoiceType::class, array(
                'label' => 'Does the user have an account?',
                'required' => true,
                'choices'  => [
                    'Yes' => true,
                    'No' => false,
                ],
            ))
            ->add('username', TextType::class, array(
                'label' => 'Please enter the username',
                'required' => false,
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'Submit',
                'attr' => array('class' => 'submitButton')
            ));
    }
}

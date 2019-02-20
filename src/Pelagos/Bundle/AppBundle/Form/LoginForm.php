<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * The login form.
 */
class LoginForm extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options.
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', TextType::class, array(
                'label' => 'Username',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Username',
                    'class' => 'form-control',
                    'autofocus' => true,
                ),
            ))
            ->add('_password', PasswordType::class, array(
                'label' => 'Password',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Password',
                    'class' => 'form-control',
                ),
            
            ));
    }
}
<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * A form for creating Funding Organizations.
 */
class DataRepositoryType extends AbstractType
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
        ->add('name', TextType::class, array(
                'label' => 'Name:',
            ))
            ->add('emailAddress', TextType::class, array(
                'label' => 'E-Mail Address:',
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'Description:',
                'attr' => array('rows' => 5),
            ))
            ->add('url', TextType::class, array(
                'label' => 'Website:',
            ))
            ->add('phoneNumber', TextType::class, array(
                'label' => 'Phone Number:',
            ))
            ->add('deliveryPoint', TextareaType::class, array(
                'attr' => array('rows' => 3, 'maxlength' => 300),
                'label' => 'Delivery Point:',
            ))
            ->add('city', TextType::class, array(
                'label' => 'City:',
            ))
            ->add('administrativeArea', TextType::class, array(
                'label' => 'State/Province:',
            ))
            ->add('postalCode', TextType::class, array(
                'label' => 'Postal Code:',
            ))
            ->add('country', TextType::class, array(
                'label' => 'Country:',
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
        $resolver->setDefaults(array(
            'data_class' => 'Pelagos\Entity\DataRepository',
            'allow_extra_fields' => true,
        ));
    }
}

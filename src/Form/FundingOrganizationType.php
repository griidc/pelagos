<?php

namespace App\Form;

use App\Entity\DataRepository;
use App\Entity\Funder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * A form for creating Funding Organizations.
 */
class FundingOrganizationType extends AbstractType
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
            ->add('shortName', TextType::class, array(
                'label' => 'Short Name:',
                'required' => false
            ))
            ->add('dataRepository', EntityType::class, array(
                'label' => 'Data Repository',
                'class' => DataRepository::class,
                'choice_label' => 'name',
                'placeholder' => '[Please Select a Data Repository]',
            ))
            ->add('defaultFunder', EntityType::class, array(
                'label' => 'Default Funder',
                'class' => Funder::class,
                'choice_label' => 'name',
                'placeholder' => '[Please Select a Default Funder]',
            ))
            ->add('emailAddress', TextType::class, array(
                'label' => 'E-Mail Address:',
                'required' => false,
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'Description:',
                'attr' => array('rows' => 5),
                'required' => false,
            ))
            ->add('url', TextType::class, array(
                'label' => 'Website:',
                'required' => false,
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
            ->add('sortOrder', IntegerType::class, array(
                'label' => 'Sort Order:',
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
        $resolver->setDefaults(array(
            'data_class' => 'App\Entity\FundingOrganization',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ));
    }
}

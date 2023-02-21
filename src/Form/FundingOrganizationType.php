<?php

namespace App\Form;

use App\Entity\DataRepository;
use App\Entity\Funder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A form for creating Funding Organizations.
 */
class FundingOrganizationType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param array $options the options
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name', TextType::class, [
                'label' => 'Name:',
            ])
            ->add('shortName', TextType::class, [
                'label' => 'Short Name:',
                'required' => false,
            ])
            ->add('dataRepository', EntityType::class, [
                'label' => 'Data Repository',
                'class' => DataRepository::class,
                'choice_label' => 'name',
                'placeholder' => '[Please Select a Data Repository]',
            ])
            ->add('defaultFunder', EntityType::class, [
                'label' => 'Default Funder',
                'class' => Funder::class,
                'choice_label' => 'name',
                'placeholder' => '[Please Select a Default Funder]',
                'required' => true,
            ])
            ->add('emailAddress', TextType::class, [
                'label' => 'E-Mail Address:',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description:',
                'attr' => ['rows' => 5],
                'required' => false,
            ])
            ->add('url', TextType::class, [
                'label' => 'Website:',
                'required' => false,
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Phone Number:',
                'required' => false,
            ])
            ->add('deliveryPoint', TextareaType::class, [
                'attr' => ['rows' => 3, 'maxlength' => 300],
                'label' => 'Delivery Point:',
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label' => 'City:',
                'required' => false,
            ])
            ->add('administrativeArea', TextType::class, [
                'label' => 'State/Province:',
                'required' => false,
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Postal Code:',
                'required' => false,
            ])
            ->add('country', TextType::class, [
                'label' => 'Country:',
                'required' => false,
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Sort Order:',
                'required' => false,
            ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver the resolver for the options
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\FundingOrganization',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}

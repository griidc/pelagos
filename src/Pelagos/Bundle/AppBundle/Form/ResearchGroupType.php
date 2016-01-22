<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * A form for creating research groups.
 */
class ResearchGroupType extends AbstractType
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
            ->add('name', TextType::class)
            ->add('fundingCycle', EntityType::class, array(
                'label' => 'Funding Cycle',
                'class' => 'Pelagos:FundingCycle',
                'choice_label' => 'name',
            ))
            ->add('url', TextType::class, array('required' => false))
            ->add('phoneNumber', TextType::class, array('required' => false))
            ->add('deliveryPoint', TextType::class, array('required' => false))
            ->add('city', TextType::class, array('required' => false))
            ->add('administrativeArea', TextType::class, array('required' => false))
            ->add('postalCode', TextType::class, array('required' => false))
            ->add('country', TextType::class, array('required' => false))
            ->add('description', TextType::class, array('required' => false))
            ->add('logo', FileType::class, array('required' => false))
            ->add('emailAddress', TextType::class, array('required' => false))
            ->add('save', SubmitType::class, array('label' => 'Create Research Group'));
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
            'data_class' => 'Pelagos\Entity\ResearchGroup',
        ));
    }
}

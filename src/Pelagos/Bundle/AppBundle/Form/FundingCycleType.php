<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * A form for creating research groups.
 */
class FundingCycleType extends AbstractType
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
            ->add('fundingCycle', EntityType::class, array(
                'label' => 'Funding Organization',
                'class' => 'Pelagos:FundingOrganization',
                'choice_label' => 'name',
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'Description:',
                'attr' => array('rows' => 5),
                'required' => false,
            ))
            ->add('url', TextType::class, array(
                'label' => 'Website',
                'required' => false,
            ))
            ->add('startDate', DateType::class, array(
                'label' => 'Start Date:',
                'required' => false,
                'widget' => 'single_text',
            ))
            ->add('endDate', DateType::class, array(
                'label' => 'End Date:',
                'required' => false,
                'widget' => 'single_text',
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
            'data_class' => 'Pelagos\Entity\FundingCycle',
            'allow_extra_fields' => true,
        ));
    }
}

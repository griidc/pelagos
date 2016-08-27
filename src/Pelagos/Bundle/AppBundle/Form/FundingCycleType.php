<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
            ->add('id', TextType::class, array(
                'label' => 'Funding Cycle ID:',
                'required' => false,
            ))
            ->add('name', TextType::class, array(
                'label' => 'Name:',
            ))
            ->add('udiPrefix', TextType::class, array(
                'label' => 'UDI Prefix:',
            ))
            ->add('fundingOrganization', EntityType::class, array(
                'label' => 'Funding Organization:',
                'class' => 'Pelagos:FundingOrganization',
                'choice_label' => 'name',
                'placeholder' => '[Please Select a Funding Organization]',
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
            ->add('startDate', DateType::class, array(
                'label' => 'Start Date:',
                'required' => false,
                'attr' => array('placeholder' => 'yyyy-mm-dd'),
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
            ))
            ->add('endDate', DateType::class, array(
                'label' => 'End Date:',
                'required' => false,
                'attr' => array('placeholder' => 'yyyy-mm-dd'),
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
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
            'data_class' => 'Pelagos\Entity\FundingCycle',
            'allow_extra_fields' => true,
        ));
    }
}

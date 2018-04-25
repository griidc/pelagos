<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Pelagos\Entity\DistributionPoint;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A form for creating Distribution Point.
 */
class DistributionPointType extends AbstractType
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
            ->add('dataCenter', EntityType::class, array(
                'label' => 'Distribution Contact:',
                'class' => 'Pelagos:DataCenter',
                'choice_value' => 'id',
                'choice_label' => 'organizationName',
                'placeholder' => '[Please Select a Distribution Contact]',
                'required' => true,
                'mapped' => false,
            ))
            ->add('distributionUrl', TextType::class, array(
                'label' => 'Distribution Url:',
                'required' => true,
            ))
            ->add('roleCode', ChoiceType::class, array(
                'label' => 'Role:',
                'required' => true,
                'choices' => DistributionPoint::getRoleCodeChoices(),
                'empty_data' => 'distributor',
                'expanded' => false,
                'preferred_choices' => function ($role, $value) {
                    return $value === 'Distributor';
                },
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
            'data_class' => 'Pelagos\Entity\DistributionPoint',
            'allow_extra_fields' => true,
        ));
    }
    
}

<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * A form for retrieving a count of entities.
 */
class ReportResearchGroupDatasetStatusType extends AbstractType
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
            ->add('ResearchGroupSelector', ChoiceType::class, array(
                // the word 'choices' is a reserved word in this context
                'choices' => $options['data'],
                'placeholder' => '[Select a Research Group]'))
             ->add('submit', SubmitType::class, array('label' => 'Generate Report'));
    }
}

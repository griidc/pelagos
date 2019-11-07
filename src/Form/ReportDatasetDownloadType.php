<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * A form for retrieving a count of entities.
 */
class ReportDatasetDownloadType extends AbstractType
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
            ->add(
                'startDate',
                DateType::class,
                array('label' => 'Start Date:',
                    'input' => 'datetime',
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'yyyy-MM-dd',
                    'required' => true,
                    'attr' => array(
                        'placeholder' => 'yyyy-mm-dd',
                        'class' => 'startDate'
                    )
                )
            )
            ->add(
                'endDate',
                DateType::class,
                array('label' => 'End Date:',
                    'input' => 'datetime',
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'yyyy-MM-dd',
                    'required' => true,
                    'attr' => array(
                        'placeholder' => 'yyyy-mm-dd',
                        'class' => 'endDate'
                    )
                )
            )
            ->add('submit', SubmitType::class, array('label' => 'Generate Report'));
    }
}

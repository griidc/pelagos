<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

use Pelagos\Entity\DIF;
use Pelagos\Entity\ResearchGroup;
use Pelagos\Entity\Person;

/**
 * A form for creating a DIF.
 */
class DIFType extends AbstractType
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
            ->add('researchGroup', EntityType::class, array(
                'class' => ResearchGroup::class,
                'choice_label' => 'name',
                'placeholder' => '[PLEASE SELECT A PROJECT]',
                'required' => true,
            ))
            ->add('title', TextareaType::class, array(
                'attr' => array(
                    'placeholder' => 'Dataset Title (200 Character Maximum)',
                    'rows' => '2',
                    'maxsize' => 200,
                ),
                'label' => 'Title:',
                'required' => true,
            ))
            ->add('primaryPointOfContact', EntityType::class, array(
                'class' => Person::class,
                'label' => 'Primary Point of Contact:',
                'choice_label' => function ($value, $key, $index) {
                    return $value->getLastName() . ', ' . $value->getFirstName() . ', ' . $value->getEmailAddress();
                },
                'placeholder' => '[PLEASE SELECT PROJECT FIRST]',
                'required' => true,
            ))
            ->add('secondaryPointOfContact', EntityType::class, array(
                'class' => Person::class,
                'label' => 'Secondary Point of Contact:',
                'choice_label' => function ($value, $key, $index) {
                    return $value->getLastName() . ', ' . $value->getFirstName() . ', ' . $value->getEmailAddress();
                },
                'placeholder' => '[PLEASE SELECT PROJECT FIRST]',
                'required' => false,
            ))
            ->add('abstract', TextareaType::class, array(
                'attr' => array(
                    'rows' => 6,
                    'placeholder' => 'Please provide a brief narrative describing: What, where, why, how, and when the data will be or have been collected/generated?  (4000 Character Maximum',
                    'maxlength' => 4000,
                ),
                'label' => 'Abstract:',
                'required' => false,
            ))
            ->add('fieldOfStudyEcologicalBiological', CheckboxType::class, array(
                 'label' => 'Ecological/Biological',
                 'required' => false,
            ))
            ->add('fieldOfStudyPhysicalOceanography', CheckboxType::class, array(
                 'label' => 'Physical Oceanography',
                 'required' => false,
            ))
            ->add('fieldOfStudyAtmospheric', CheckboxType::class, array(
                 'label' => 'Atmospheric',
                 'required' => false,
            ))
            ->add('fieldOfStudyChemical', CheckboxType::class, array(
                 'label' => 'Chemical',
                 'required' => false,
            ))
            ->add('fieldOfStudyHumanHealth', CheckboxType::class, array(
                 'label' => 'Human Health',
                 'required' => false,
            ))
            ->add('fieldOfStudySocialCulturalPolitical', CheckboxType::class, array(
                 'label' => 'Social/Cultural/Political',
                 'required' => false,
            ))
            ->add('fieldOfStudyEconomics', CheckboxType::class, array(
                 'label' => 'Economics',
                 'required' => false,
            ))
            ->add('fieldOfStudyOther', TextType::class, array(
                'label' => 'Other Field of Study:',
                'required' => false,
            ))
            ->add('dataSize', ChoiceType::class, array(
                'choices' => array_combine(DIF::DATA_SIZES, DIF::DATA_SIZES),
                'data' => DIF::DATA_SIZES[0],
                'label' => 'Approximate Dataset Size:',
                'required' => true,
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('variablesObserved', TextareaType::class, array(
                'attr' => array('rows' => 3),
                'label' => 'Abstract:',
                'attr' => array(
                    'placeholder' => 'Examples: wind speed (km/hr), salinity (ppt), temperature (°C), PCB concentrations in eggs from a specified species (ng/g wet weight), Ionic Strength (mM)'
                ),
                'required' => false,
            ))
            ->add('collectionMethodFieldSampling', CheckboxType::class, array(
                 'label' => 'Field Sampling',
                 'required' => false,
            ))
            ->add('collectionMethodSimulatedGenerated', CheckboxType::class, array(
                 'label' => 'Simulated/Generated',
                 'required' => false,
            ))
            ->add('collectionMethodLaboratory', CheckboxType::class, array(
                 'label' => 'Laboratory',
                 'required' => false,
            ))
            ->add('collectionMethodLiteratureBased', CheckboxType::class, array(
                 'label' => 'Literature Based',
                 'required' => false,
            ))
            ->add('collectionMethodRemoteSensing', CheckboxType::class, array(
                 'label' => 'Remote Sensing',
                 'required' => false,
            ))
            ->add('collectionMethodOther', TextType::class, array(
                'label' => 'Other Collection Method:',
                'required' => false,
            ))
            ->add('estimatedStartDate', DateType::class, array(
                'label' => 'Start Date:',
                'input' => 'datetime',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => false,
            ))
            ->add('estimatedEndDate', DateType::class, array(
                'label' => 'End Date:',
                'input' => 'datetime',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => false,
            ))
            ->add('spatialExtentDescription', TextType::class, array(
                'label' => 'Description:',
                'attr' => array(
                    'placeholder' => 'Example - "lab measurements of oil degradation, no field sampling involved"'
                ),
                'required' => false,
            ))
            ->add('spatialExtentGeometry', HiddenType::class)
            ->add('nationalDataArchiveNODC', CheckboxType::class, array(
                 'label' => 'National Oceanographic Data Center',
                 'required' => false,
            ))
            ->add('nationalDataArchiveStoret', CheckboxType::class, array(
                 'label' => 'US EPA Storet',
                 'required' => false,
            ))
            ->add('nationalDataArchiveGBIF', CheckboxType::class, array(
                 'label' => 'Global Biodiversity Information Facility',
                 'required' => false,
            ))
            ->add('nationalDataArchiveNCBI', CheckboxType::class, array(
                 'label' => 'National Center for Biotechnology Information',
                 'required' => false,
            ))
            ->add('nationalDataArchiveDataGov', CheckboxType::class, array(
                 'label' => 'Data.gov Dataset Management System',
                 'required' => false,
            ))
            ->add('nationalDataArchiveOther', TextType::class, array(
                'label' => 'Other National Data Archive:',
                'required' => false,
            ))
            ->add('ethicalIssues', ChoiceType::class, array(
                'choices' => array_combine(DIF::ETHICAL_ISSUES, DIF::ETHICAL_ISSUES),
                'expanded' => true,
                'multiple' => false,
                'required' => false,
            ))
            ->add('ethicalIssuesExplanation', TextType::class, array(
                'label' => 'If yes or uncertain, please explain:',
                'required' => false,
            ))
            ->add('remarks', TextareaType::class, array(
                'attr' => array('rows' => 3),
                'label' => 'Remarks:',
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
            'data_class' => 'Pelagos\Entity\DIF',
            'allow_extra_fields' => true,
        ));
    }

    /**
     * Finish the form view.
     *
     * This overrides the empty finishView in AbstractType and removes the POC choices.
     *
     * @param FormView      $view    The view.
     * @param FormInterface $form    The form.
     * @param array         $options The options.
     *
     * @return void
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
       $view->children['primaryPointOfContact']->vars['choices'] = array();
       $view->children['secondaryPointOfContact']->vars['choices'] = array();
    }
}

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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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
            ->add('status', HiddenType::class)
            ->add('researchGroup', EntityType::class, array(
                'class' => ResearchGroup::class,
                'choice_label' => 'name',
                'placeholder' => '[PLEASE SELECT A PROJECT]',
                'required' => true,
            ))
            ->add('title', TextType::class, array(
                'attr' => array('Placeholder' => ''),
                'label' => 'Title:',
                'attr' => array(
                    'placeholder' => 'Dataset Title (200 Character Maximum)',
                    'rows' => '2',
                    'maxsize' => 200,
                ),
                'required' => true,
            ))
            // the following will need choiceloader to constrain list, not choice_label, but it is undocumented.
            ->add('primaryPointOfContact', EntityType::class, array(
                'class' => Person::class,
                'label' => 'Primary POC:',
                'choices' => array(),
                'placeholder' => '[PLEASE SELECT PROJECT FIRST]',
                'required' => true,
            ))
            ->add('secondaryPointOfContact', EntityType::class, array(
                'class' => Person::class,
                'label' => 'Secondary POC:',
                'choice_label' => function ($value, $key, $index) {
                    return $value->getLastName() . ',' . $value->getFirstName() . '(' . $value->getEmailAddress() . ')';
                },
                'required' => true,
            ))
            ->add('abstract', TextareaType::class, array(
                'attr' => array('rows' => 6, 'maxlength' => 4000),
                'label' => 'Abstract:',
                'attr' => array(
                    'placeholder' => 'Please provide a brief narrative describing: What, where, why, how, and when the data will be or have been collected/generated?  (4000 Character Maximum'),
                'required' => false,
            ))
            ->add('fieldOfStudyEcologicalBiological', ChoiceType::class, array(
                 'choices' => array('Ecological/Biological' => 'Ecological/Biological'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('fieldOfStudyPhysicalOceanography', ChoiceType::class, array(
                 'choices' => array('Physical Oceanography' => 'Physical Oceanography'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('fieldOfStudyAtmospheric', ChoiceType::class, array(
                 'choices' => array('Atmospheric' => 'Atmospheric'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('fieldOfStudyChemical', ChoiceType::class, array(
                 'choices' => array('Chemical' => 'Chemical'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('fieldOfStudyHumanHealth', ChoiceType::class, array(
                 'choices' => array('Human Health' => 'Human Health'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('fieldOfStudySocialCulturalPolitical', ChoiceType::class, array(
                 'choices' => array('Social/Cultural/Political' => 'Social/Cultural/Political'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('fieldOfStudyEconomics', ChoiceType::class, array(
                 'choices' => array('Economics' => 'Economics'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('fieldOfStudyOther', TextType::class, array(
                'label' => 'Other Field of Study:',
                'required' => false,
            ))
            ->add('dataSize', ChoiceType::class, array(
                'choices' => array_combine(DIF::DATA_SIZES, DIF::DATA_SIZES),
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
            ->add('collectionMethodFieldSampling', ChoiceType::class, array(
                 'choices' => array('Field Sampling' => 'Field Sampling'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('collectionMethodSimulatedGenerated', ChoiceType::class, array(
                 'choices' => array('Simulated/Generated' => 'Simulated/Generated'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('collectionMethodLaboratory', ChoiceType::class, array(
                 'choices' => array('Laboratory' => 'Laboratory'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('collectionMethodLiteratureBased', ChoiceType::class, array(
                 'choices' => array('Literature Based' => 'Literature Based'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('collectionMethodRemoteSensing', ChoiceType::class, array(
                 'choices' => array('Remote Sensing' => 'Remote Sensing'),
                 'expanded' => true,
                 'multiple' => true,
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
                'required' => false,
                'label' => 'National Oceanographic Data Center',
            ))
            ->add('nationalDataArchiveStoret', ChoiceType::class, array(
                 'choices' => array('US EPA Storet' => 'US EPA Storet'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('nationalDataArchiveGBIF', ChoiceType::class, array(
                 'choices' => array('Global Biodiversity Information Facility' => 'Global Biodiversity Information Facility'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('nationalDataArchiveNCBI', ChoiceType::class, array(
                 'choices' => array('National Center for Biotechnology Information' => 'National Center for Biotechnology Information'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('nationalDataArchiveDataGov', ChoiceType::class, array(
                 'choices' => array('Data.gov Dataset Management System' => 'Data.gov Dataset Management System'),
                 'expanded' => true,
                 'multiple' => true,
                 'required' => false,
            ))
            ->add('nationalDataArchiveOther', TextType::class, array(
                'label' => 'Other National Data Archive:',
                'required' => false,
            ))
            ->add('ethicalIssues', ChoiceType::class, array(
                'choices' => DIF::ETHICAL_ISSUES,
                'expanded' => true,
                'multiple' => false,
                'required' => true,
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
}

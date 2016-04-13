<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

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
                'class' => '\Pelagos\Entity\ResearchGroup',
                'choice_label' => 'name',
                'placeholder' => 'Select a Research Group',
                'required' => true,
            ))
            ->add('title', TextType::class, array(
                'attr' => array('Placeholder' => ''),
                'label' => 'Title:',
                'attr' => array(
                    'placeholder' => 'Dataset Title (200 Character Maximum)',
                    'rows' => '2',
                ),
                'required' => true,
            ))
            // the following will need choiceloader to constrain list, not choice_label, but it is undocumented.
            ->add('primaryPointOfContact', EntityType::class, array(
                'class' => '\Pelagos\Entity\Person',
                'label' => 'Primary POC:',
                'choice_label' => function ($value, $key, $index) {
                    return $value->lastName . ',' . $value->firstName . '(' . $value->emailAddress . ')';
                },
                'required' => true,
            ))
            ->add('secondaryPointOfContact', EntityType::class, array(
                'class' => '\Pelagos\Entity\Person',
                'label' => 'Secondary POC:',
                'choice_label' => function ($value, $key, $index) {
                    return $value->lastName . ',' . $value->firstName . '(' . $value->emailAddress . ')';
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
                'choices' => [
                        new Category('Ecological/Biological'),
                 ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('fieldOfStudyPhysicalOceanography', ChoiceType::class, array(
                'choices' => [
                        new Category('Physical Oceanography'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('fieldOfStudyAtmospheric', ChoiceType::class, array(
                'choices' => [
                        new Category('Atmospheric'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('fieldOfStudyChemical', ChoiceType::class, array(
                'choices' => [
                        new Category('Chemical'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('fieldOfStudyHumanHealth', ChoiceType::class, array(
                'choices' => [
                        new Category('Human Health'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('fieldOfStudySocialCulturalPolitical', ChoiceType::class, array(
                'choices' => [
                        new Category('Social/Cultural/Political'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('fieldOfStudyEconomics', ChoiceType::class, array(
                'choices' => [
                        new Category('Economics'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('fieldOfStudyOther', TextType::class, array(
                'label' => 'Other Field of Study:',
                'required' => false,
            ))
            ->add('dataSize', TextType::class, array(
                'label' => 'Approximate Dataset Size:',
                'required' => false,
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
                'choices' => [
                        new Category('Field Sampling'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('collectionMethodSimulatedGenerated', ChoiceType::class, array(
                'choices' => [
                        new Category('Simulated/Generated'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('collectionMethodLaboratory', ChoiceType::class, array(
                'choices' => [
                        new Category('Laboratory'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('collectionMethodLiteratureBased', ChoiceType::class, array(
                'choices' => [
                        new Category('Literature Based'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('collectionMethodRemoteSensing', ChoiceType::class, array(
                'choices' => [
                        new Category('Remote Sensing'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('CollectionMethodOther', TextType::class, array(
                'label' => 'Other Collection Method:',
                'required' => false,
            ))
            ->add('estimatedStartDate', DateType::class, array(
                'label' => 'Start Date:',
                'input' => 'datetime',
                'widget' => 'choice',
                'required' => false,
            ))
            ->add('estimatedEndDate', DateType::class, array(
                'label' => 'End Date:',
                'input' => 'datetime',
                'widget' => 'choice',
                'required' => false,
            ))
            ->add('spacialDescription', TextType::class, array(
                'label' => 'Description:',
                'attr' => array(
                    'placeholder' => 'Example - "lab measurements of oil degradation, no field sampling involved"'
                ),
                'required' => false,
            ))
            ->add('spacialGeometry', HiddenType::class)
            ->add('nationalDataArchiveNODC', ChoiceType::class, array(
                'choices' => [
                        new Category('National Oceanographic Data Center'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('nationalDataArchiveUSEPAStoret', ChoiceType::class, array(
                'choices' => [
                        new Category('US EPA Storet'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('nationalDataArchiveGBIF', ChoiceType::class, array(
                'choices' => [
                        new Category('Global Biodiversity Information Facility'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('nationalDataArchiveNCBI', ChoiceType::class, array(
                'choices' => [
                        new Category('National Center for Biotechnology Information'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('nationalDataArchiveDataGov', ChoiceType::class, array(
                'choices' => [
                        new Category('Data.gov Dataset Management System'),
                    ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ))
            ->add('NationalDataArchiveOther', TextType::class, array(
                'label' => 'Other National Data Archive:',
                'required' => false,
            ))
            ->add('ethicalIssues', ChoiceType::class, array(
                'choices' => [
                        new Category('No'),
                        new Category('Yes'),
                        new Category('Uncertain'),
                    ],
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

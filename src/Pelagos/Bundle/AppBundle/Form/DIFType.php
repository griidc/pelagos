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
            ->add('fieldOfStudy', ChoiceType::class, array(
                'choices' => [
                        new Category('Ecological/Biological'),
                        new Category('Physical Oceanography'),
                        new Category('Atmospheric'),
                        new Category('Chemical'),
                        new Category('Human Health'),
                        new Category('Social/Cultural/Political'),
                        new Category('Economics'),
                    ],
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
            ->add('CollectionMethod', ChoiceType::class, array(
                'choices' => [
                        new Category('Field Sampling'),
                        new Category('Simulated/Generated'),
                        new Category('Laboratory'),
                        new Category('Literature Based'),
                        new Category('Remote Sensing'),
                    ],
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
            ->add('NationalDataArchive', ChoiceType::class, array(
                'choices' => [
                        new Category('NODC'),
                        new Category('US EPA Storet'),
                        new Category('GBIF'),
                        new Category('NCBI'),
                        new Category('Data Gov'),
                    ],
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
}

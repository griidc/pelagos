<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Pelagos\Entity\DatasetSubmission;

/**
 * A form type for creating a Dataset Submission form.
 */
class DatasetSubmissionType extends AbstractType
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
        $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
        $builder
            ->add('title', TextType::class, array(
                'label' => 'Dataset Title:',
                'required' => true,
            ))
            ->add('abstract', TextareaType::class, array(
                'label' => 'Dataset Abstract:',
                'required' => true,
            ))
            ->add('authors', TextType::class, array(
                'label' => 'Dataset Author(s):',
                'required' => true,
            ))
            ->add('pointOfContactName', TextType::class, array(
                'label' => 'Name:',
                'required' => true,
            ))
            ->add('pointOfContactEmail', TextType::class, array(
                'label' => 'E-Mail:',
                'required' => true,
            ))
            ->add('restrictions', ChoiceType::class, array(
                'choices' => DatasetSubmission::RESTRICTIONS,
                'data' => DatasetSubmission::RESTRICTION_NONE,
                'label' => 'Restrictions:',
                'placeholder' => false,
                'required' => false,
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('doi', TextType::class, array(
                'label' => 'Digital Object Identifier:',
                'required' => false,
                 'attr' => array('size' => '60'),
            ))
            ->add('datasetFileTransferType', HiddenType::class, array(
                'required' => false,
            ))
            ->add('datasetFile', FileType::class, array(
                'label' => 'Dataset File:',
                'required' => false,
            ))
            ->add('datasetFilePath', TextType::class, array(
                'label' => 'Dataset File Path:',
                'required' => false,
            ))
            ->add('datasetFileUrl', TextType::class, array(
                'label' => 'Dataset File URL:',
                'required' => false,
            ))
            ->add('datasetFileAvailabilityDate', DateType::class, array(
                'label' => 'Availability Date:',
                'input' => 'datetime',
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => false,
            ))
            ->add('datasetFilePullCertainTimesOnly', ChoiceType::class, array(
                'choices' => array('Yes' => true, 'No' => false),
                'data' => false,
                'label' => 'Download Certain Times Only:',
                'required' => false,
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('datasetFilePullStartTime', TimeType::class, array(
                'label' => 'Start Time:',
                'input' => 'datetime',
                'widget' => 'choice',
                'minutes' => array(0, 15, 30, 45),
                'html5' => false,
                'required' => false,
            ))
            ->add('datasetFilePullDays', ChoiceType::class, array(
                'choices' => array_combine($days, $days),
                'label' => 'Weekdays:',
                'data' => $days,
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ))
            ->add('datasetFilePullSourceData', ChoiceType::class, array(
                'choices' => array('Yes' => true, 'No' => false),
                'data' => true,
                'label' => 'Pull Source Data:',
                'required' => false,
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('metadataFileTransferType', HiddenType::class, array(
                'required' => false,
            ))
            ->add('metadataFile', FileType::class, array(
                'label' => 'Metadata File:',
                'required' => false,
            ))
            ->add('metadataFilePath', TextType::class, array(
                'label' => 'Metadata File Path:',
                'required' => false,
            ))
            ->add('metadataFileUrl', TextType::class, array(
                'label' => 'Metadata File URL:',
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
            'data_class' => 'Pelagos\Entity\DatasetSubmission',
            'allow_extra_fields' => true,
        ));
    }
}

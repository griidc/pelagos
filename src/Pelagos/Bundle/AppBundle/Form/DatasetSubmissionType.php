<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Entity;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;

/**
 * A form type for creating a Dataset Submission form.
 */
class DatasetSubmissionType extends AbstractType
{
    /**
     * Constructor for form type.
     *
     * @param Entity                                $entity The entity associated with this form.
     * @param PersonDatasetSubmissionDatasetContact $poc    A point of contact.
     */
    public function __construct(Entity $entity = null, PersonDatasetSubmissionDatasetContact $poc = null)
    {
        $this->formEntity = $entity;
        $this->formPoc = $poc;
    }

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
            ->add('title', Type\TextType::class, array(
                'label' => 'Dataset Title',
                'required' => true,
            ))
            ->add('abstract', Type\TextareaType::class, array(
                'label' => 'Dataset Abstract',
                'required' => true,
                'attr' => array('rows' => '16'),
            ))
            ->add('authors', Type\TextType::class, array(
                'label' => 'Dataset Author(s)',
                'required' => true,
            ))
            ->add('restrictions', Type\ChoiceType::class, array(
                'choices' => DatasetSubmission::getRestrictionsChoices(),
                'label' => 'Restrictions',
                'placeholder' => false,
                'required' => false,
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('datasetFileUri', Type\HiddenType::class, array(
                'required' => true,
                'attr' => array('data-msg-required' => 'You must provide a dataset file using one of the methods below.'),
            ))
            ->add('datasetFileTransferType', Type\HiddenType::class, array(
                'required' => false,
            ))
            ->add('datasetFilePath', Type\TextType::class, array(
                'label' => 'Dataset File Path',
                'required' => false,
                'mapped' => false,
                'attr' => array('disabled' => 'disabled'),
            ))
            ->add('datasetFileForceImport', Type\CheckboxType::class, array(
                'label' => 'import this file again from the same path',
                'required' => false,
                'mapped' => false,
            ))
            ->add('datasetFileUrl', Type\TextType::class, array(
                'label' => 'Dataset File URL',
                'required' => false,
                'mapped' => false,
                'attr' => array('data-rule-url' => true),
            ))
            ->add('datasetFileForceDownload', Type\CheckboxType::class, array(
                'label' => 'download this file again from the same URL',
                'required' => false,
                'mapped' => false,
            ))
            ->add('shortTitle', Type\TextType::class, array(
                'label' => 'Short Title',
                'required' => false,
            ))
            ->add('referenceDate', Type\DateType::class, array(
                'label' => 'Date',
                'required' => true,
                'attr' => array('placeholder' => 'yyyy-mm-dd'),
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => true,
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC',
            ))
            ->add('referenceDateType', Type\ChoiceType::class, array(
                'label' => 'Date Type',
                'choices' => DatasetSubmission::getReferenceDateTypeChoices(),
                'placeholder' => '[Please Select a Date Type]',
                'required' => true,
            ))
            ->add('purpose', Type\TextareaType::class, array(
                'label' => 'Purpose',
                'required' => true,
                'attr' => array('rows' => '5'),
            ))
            ->add('suppParams', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Data Parameters and Units',
                'required' => true,
                'attr' => array('rows' => '5'),
            ))
            ->add('suppMethods', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Methods',
                'required' => false,
                'attr' => array('rows' => '5'),
            ))
            ->add('suppInstruments', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Instruments',
                'required' => false,
                'attr' => array('rows' => '5'),
            ))
            ->add('suppSampScalesRates', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Sampling Scales and Rates',
                'required' => false,
                'attr' => array('rows' => '5'),
            ))
            ->add('suppErrorAnalysis', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Error Analysis',
                'required' => false,
                'attr' => array('rows' => '5'),
            ))
            ->add('suppProvenance', Type\TextareaType::class, array(
                'label' => 'Supplemental Information - Provenance and Historical References',
                'required' => false,
                'attr' => array('rows' => '5'),
            ))
            ->add('themeKeywords', Type\CollectionType::class, array(
                'label' => 'Theme Keywords',
                'entry_type' => Type\TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => true,
            ))
            ->add('placeKeywords', Type\CollectionType::class, array(
                'label' => 'Place Keywords',
                'entry_type' => Type\TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => false,
            ))
            ->add('topicKeywords', Type\ChoiceType::class, array(
                'label' => 'Topic Category Keywords',
                'choices' => DatasetSubmission::getTopicKeywordsChoices(),
                'multiple' => true,
                'required' => true,
            ))
            ->add('spatialExtent', Type\HiddenType::class, array(
                'required' => true,
            ))
            ->add('spatialExtentDescription', Type\TextareaType::class, array(
                'label' => 'Spatial Extent Description',
                'required' => false,
                'attr' => array('rows' => '5'),
            ))
            ->add('temporalExtentDesc', Type\ChoiceType::class, array(
                'label' => 'Time Period Description',
                'choices' => DatasetSubmission::getTemporalExtentDescChoices(),
                'required' => true,
                'placeholder' => '[Please Select a Time Period Description]',
            ))
            ->add('temporalExtentBeginPosition', Type\DateType::class, array(
                'label' => 'Start Date',
                'required' => true,
                'attr' => array('placeholder' => 'yyyy-mm-dd'),
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => true,
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC',
            ))
            ->add('temporalExtentEndPosition', Type\DateType::class, array(
                'label' => 'End Date',
                'required' => true,
                'attr' => array('placeholder' => 'yyyy-mm-dd'),
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => true,
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC',
            ))
            ->add('distributionFormatName', Type\TextType::class, array(
                'label' => 'Distribution Format Name',
                'required' => false,
            ))
            ->add('fileDecompressionTechnique', Type\TextType::class, array(
                'label' => 'File Decompression Technique',
                'required' => false,
            ))
            ->add('datasetContacts', Type\CollectionType::class, array(
                'label' => 'Dataset Contacts',
                'entry_type' => PersonDatasetSubmissionType::class,
                'entry_options' => array(
                    'data_class' => PersonDatasetSubmissionDatasetContact::class,
                ),
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => true,
            ))
            ->add('submitButton', Type\SubmitType::class, array(
                'label' => 'Submit',
                'attr'  => array('class' => 'submitButton'),
            ))
            ->add('endReviewBtn', Type\SubmitType::class, array(
                'label' => 'End Review',
                'attr'  => array('class' => 'submitButton'),
             ))
            ->add('acceptDatasetBtn', Type\SubmitType::class, array(
                'label' => 'Accept Dataset',
                'attr'  => array('class' => 'submitButton'),
            ))
            ->add('requestRevisionsBtn', Type\SubmitType::class, array(
                'label' => 'Request Revisions',
                'attr'  => array('class' => 'submitButton'),
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
        $entity = $this->formEntity;
        $poc = $this->formPoc;

        $resolver->setDefaults(array(
            'data_class' => DatasetSubmission::class,
            'allow_extra_fields' => true,
            'empty_data' => function (FormInterface $form) use ($entity, $poc) {
                return new DatasetSubmission($entity, $poc);
            },
        ));
    }
}

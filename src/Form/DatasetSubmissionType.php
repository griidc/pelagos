<?php

namespace App\Form;

use App\Entity\DatasetLink;
use App\Entity\DatasetSubmission;
use App\Entity\DistributionPoint;
use App\Entity\Entity;
use App\Entity\Funder;
use App\Entity\Keyword;
use App\Entity\PersonDatasetSubmissionDatasetContact;
use App\Entity\PersonDatasetSubmissionMetadataContact;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A form type for creating a Dataset Submission form.
 *
 * @extends AbstractType<DatasetSubmission>
 */
class DatasetSubmissionType extends AbstractType
{
    /**
     * Constructor for form type.
     *
     * @param Entity                                $entity the entity associated with this form
     * @param PersonDatasetSubmissionDatasetContact $poc    a point of contact
     */
    public function __construct(Entity $entity = null, PersonDatasetSubmissionDatasetContact $poc = null)
    {
        $this->formEntity = $entity;
        $this->formPoc = $poc;
    }

    /**
     * Builds the form.
     *
     * @param array $options the options
     *
     * @see FormTypeExtensionInterface::buildForm()
     *
     * @return void
     */
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $builder
            ->add('title', Type\TextareaType::class, [
                'label' => 'Dataset Title',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Please provide a title that describes the contents of your dataset. This is NOT the title of your project. (200 character maximum)',
                    'rows' => '2',
                    'maxsize' => 200,
                ],
            ])
            ->add('funders', EntityType::class, [
                'label' => 'Funder',
                'class' => Funder::class,
                'choice_label' => function (Funder $funder) {
                    return $funder->getName();
                },
                'query_builder' => function (EntityRepository $repo) {
                    return $repo->createQueryBuilder('funder')
                        ->orderBy('funder.name', 'ASC');
                },
                'multiple' => true,
                'attr' => [
                    'placeholder' => 'Please select a funder.',
                ],
            ])
            // ->add('funders', Type\CollectionType::class, [
            //     'label' => 'Funders',
            //     'entry_type' => EntityType::class,
            //     'entry_options' => [
            //         'class' => Funder::class,
            //     ],
            //     'by_reference' => true,
            //     'allow_add' => true,
            //     'allow_delete' => true,
            //     'delete_empty' => true,
            //     'required' => false,
            // ])
            ->add('additionalFunders', Type\TextType::class, [
                'label' => 'Additional Funders',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Please add any additional funders not on the dropdown.',
                ],
            ])
            ->add('abstract', Type\TextareaType::class, [
                'label' => 'Dataset Abstract',
                'required' => true,
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'Please provide a brief narrative describing what, where, why, how, and when the data will be or have been collected or generated. (4000 character maximum)',
                    'maxlength' => 4000,
                ],
            ])
            ->add('authors', Type\TextType::class, [
                'label' => 'Dataset Author(s)',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Please provide a list of authors who should be acknowledged if these data are cited in published materials.',
                ],
            ])
            ->add('restrictions', Type\ChoiceType::class, [
                'choices' => DatasetSubmission::getRestrictionsChoices(),
                'label' => 'Restrictions',
                'placeholder' => false,
                'required' => false,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('datasetFileTransferType', Type\HiddenType::class, [
                'required' => false,
                'data' => DatasetSubmission::TRANSFER_TYPE_HTTP,
            ])
            ->add('shortTitle', Type\TextType::class, [
                'label' => 'Short Title',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Please provide a short name or alternative title for the dataset.',
                ],
            ])
            ->add('purpose', Type\TextareaType::class, [
                'label' => 'Purpose',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Please provide a summary of the reason why the data were collected or generated.',
                    'rows' => '5',
                ],
            ])
            ->add('suppParams', Type\TextareaType::class, [
                'label' => 'Data Parameters and Units',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Please provide a list of the parameters/variables and units included in the dataset.',
                    'rows' => '5'
                ],
            ])
            ->add('suppMethods', Type\TextareaType::class, [
                'label' => 'Methods',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Please provide a brief description of methods used to acquire the data included in the dataset.',
                    'rows' => '5',
                ],
            ])
            ->add('suppInstruments', Type\TextareaType::class, [
                'label' => 'Instruments',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Please provide a list of instruments used to acquire the data included in the dataset.',
                    'rows' => '5',
                ],
            ])
            ->add('suppSampScalesRates', Type\TextareaType::class, [
                'label' => 'Sampling Scales and Rates',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Please provide a description of spatial and temporal scales and rates that were used to collect or generate the data.',
                    'rows' => '5'
                ],
            ])
            ->add('suppErrorAnalysis', Type\TextareaType::class, [
                'label' => 'Error Analysis',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Please provide a description of any error or uncertainty analysis completed on the final data and the results of the analysis.',
                    'rows' => '5',
                ],
            ])
            ->add('suppProvenance', Type\TextareaType::class, [
                'label' => 'Provenance and Historical References',
                'required' => false,
                'attr' => [
                    'placeholder' => 'If existing historical were used as part of the dataset, please provide a description of these data including who created the original dataset (person and/or organization and where the historical data be obtained).',
                    'rows' => '5'
                ],
            ])
            ->add('themeKeywords', Type\CollectionType::class, [
                'label' => 'Theme Keywords',
                'entry_type' => Type\TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Please provide commonly used words or short phrases that describe themes or subjects that describe the dataset.',
                ],
            ])
            ->add('placeKeywords', Type\CollectionType::class, [
                'label' => 'Place Keywords',
                'entry_type' => Type\TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Please provide commonly used words or short phrases that describe the geographic areas or locations that describe the dataset.',
                ],
            ])
            ->add('topicKeywords', Type\ChoiceType::class, [
                'label' => 'Topic Category Keywords',
                'choices' => DatasetSubmission::getTopicKeywordsChoices(),
                'choice_attr' => function (mixed $choice) {
                    return ['description' => DatasetSubmission::TOPIC_KEYWORDS[$choice]['description']];
                },
                'multiple' => true,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Please provide broad theme keywords pre-defined by the ISO 19115-2 metadata standard used by GRIIDC.',
                ],
            ])
            ->add('keywords', Type\CollectionType::class, [
                'label' => 'Keywords',
                'entry_type' => EntityType::class,
                'entry_options' => [
                    'class' => Keyword::class,
                ],
                'by_reference' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => false,
            ])
            ->add('spatialExtent', Type\HiddenType::class, [
                'required' => true,
            ])
            ->add('spatialExtentDescription', Type\TextareaType::class, [
                'label' => 'Spatial Extent Description',
                'required' => false,
                'attr' => ['rows' => '5'],
            ])
            ->add('temporalExtentNilReasonType', Type\ChoiceType::class, [
                'label' => 'Nilreason Type',
                'choices' => DatasetSubmission::getNilReasonTypes(),
                'required' => 'false',
                'placeholder' => '[Please Select a Reason]',
            ])
            ->add('temporalExtentDesc', Type\ChoiceType::class, [
                'label' => 'Time Period Description',
                'choices' => DatasetSubmission::getTemporalExtentDescChoices(),
                'required' => true,
                'placeholder' => 'Please select a time period description.',
            ])
            ->add('temporalExtentBeginPosition', Type\DateType::class, [
                'label' => 'Start Date',
                'attr' => ['placeholder' => 'yyyy-mm-dd'],
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => true,
            ])
            ->add('temporalExtentEndPosition', Type\DateType::class, [
                'label' => 'End Date',
                'attr' => ['placeholder' => 'yyyy-mm-dd'],
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'required' => true,
            ])
            ->add('distributionFormatName', Type\TextType::class, [
                'label' => 'Distribution Format Name',
                'required' => false,
            ])
            ->add('fileDecompressionTechnique', Type\TextType::class, [
                'label' => 'File Decompression Technique',
                'required' => false,
            ])
            ->add('datasetLinks', Type\CollectionType::class, [
                'label' => 'Dataset Links',
                'entry_type' => DatasetLinkType::class,
                'entry_options' => [
                    'data_class' => DatasetLink::class,
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => true,
            ])
            ->add('datasetContacts', Type\CollectionType::class, [
                'label' => 'Dataset Contacts',
                'entry_type' => PersonDatasetSubmissionType::class,
                'entry_options' => [
                    'data_class' => PersonDatasetSubmissionDatasetContact::class,
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => true,
            ])
            ->add('metadataContacts', Type\CollectionType::class, [
                'label' => 'Metadata Contacts',
                'entry_type' => PersonDatasetSubmissionType::class,
                'entry_options' => [
                    'data_class' => PersonDatasetSubmissionMetadataContact::class,
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => true,
            ])
            ->add('distributionPoints', Type\CollectionType::class, [
                'label' => 'Distribution Points',
                'entry_type' => DistributionPointType::class,
                'entry_options' => [
                    'data_class' => DistributionPoint::class,
                ],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'required' => true,
            ])
            ->add('remotelyHostedUrl', Type\TextType::class, [
                'label' => 'Remotely Hosted URL',
                'required' => false,
            ])
            ->add('isRemotelyHosted', Type\CheckboxType::class, [
                'label' => 'Is Remotely Hosted',
                'mapped' => false,
                'required' => false,
            ])
            ->add('remotelyHostedName', Type\TextType::class, [
                'label' => 'Remotely Hosted Name',
                'required' => false,
            ])
            ->add('remotelyHostedDescription', Type\TextType::class, [
                'label' => 'Remotely Hosted Description',
                'required' => false,
            ])
            ->add('remotelyHostedFunction', Type\ChoiceType::class, [
                'label' => 'Remotely Hosted Function',
                'choices' => DatasetSubmission::getOnlineFunctionCodes(),
                'placeholder' => '[Please Select]',
                'required' => false,
            ])
            ->add('isDatasetFileInColdStorage', Type\CheckboxType::class, [
                'label' => 'In Cold Storage',
                'mapped' => false,
                'required' => false,
            ])
            ->add('datasetFileColdStorageArchiveSize', Type\IntegerType::class, [
                'label' => 'Cold Storage Archive Size (Bytes)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('datasetFileColdStorageArchiveSha256Hash', Type\TextType::class, [
                'label' => 'Cold Storage Archive Sha256 Hash',
                'mapped' => false,
                'required' => false,
            ])
            ->add('datasetFileColdStorageOriginalFilename', Type\TextType::class, [
                'label' => 'Cold Storage Archive Original Filename',
                'mapped' => false,
                'required' => false,
            ])
            ->add('coldStorageTotalUnpackedCount', Type\IntegerType::class, [
                'label' => 'Cold Storage Total Unpacked File Count',
                'mapped' => false,
                'required' => false,
            ])
            ->add('coldStorageTotalUnpackedSize', Type\IntegerType::class, [
                'label' => 'Cold Storage Total Unpacked Bytes',
                'mapped' => false,
                'required' => false,
            ])
            ->add('largeFileUri', Type\HiddenType::class, [
                'label' => 'Large File Path',
                'required' => false,
                'mapped' => true,
            ])
            ->add('issueTrackingTicket', Type\TextType::class, [
                'label' => 'Issue Tracking Ticket',
                'required' => false,
            ])
            ->add('submitButton', Type\SubmitType::class, [
                'label' => 'Submit',
                'attr' => ['class' => 'submitButton'],
            ])
            ->add('endReviewBtn', Type\SubmitType::class, [
                'label' => 'End Review',
                'attr' => ['class' => 'submitButton'],
             ])
            ->add('acceptDatasetBtn', Type\SubmitType::class, [
                'label' => 'Accept Dataset',
                'attr' => ['class' => 'submitButton'],
            ])
            ->add('requestRevisionsBtn', Type\SubmitType::class, [
                'label' => 'Request Revisions',
                'attr' => ['class' => 'submitButton'],
            ]);

        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            if ($data instanceof DatasetSubmission) {
                if (true === $data->isDatasetFileInColdStorage()) {
                    $form->get('isDatasetFileInColdStorage')->setData(true);
                    $form->get('datasetFileColdStorageArchiveSize')->setData(
                        $data->getDatasetFileColdStorageArchiveSize()
                    );
                    $form->get('datasetFileColdStorageArchiveSha256Hash')->setData(
                        $data->getDatasetFileColdStorageArchiveSha256Hash()
                    );
                    $form->get('datasetFileColdStorageOriginalFilename')->setData(
                        $data->getDatasetFileColdStorageOriginalFilename()
                    );
                    $form->get('coldStorageTotalUnpackedCount')->setData(
                        $data->getColdStorageTotalUnpackedCount()
                    );
                    $form->get('coldStorageTotalUnpackedSize')->setData(
                        $data->getColdStorageTotalUnpackedSize()
                    );
                }
                if ($data->isRemotelyHosted()) {
                    $form->get('isRemotelyHosted')->setData(true);
                }
            }
        });

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $size = $event->getForm()->get('datasetFileColdStorageArchiveSize')->getData();
                $hash = $event->getForm()->get('datasetFileColdStorageArchiveSha256Hash')->getData();
                $name = $event->getForm()->get('datasetFileColdStorageOriginalFilename')->getData();
                $totalCount = $event->getForm()->get('coldStorageTotalUnpackedCount')->getData();
                $totalBytes = $event->getForm()->get('coldStorageTotalUnpackedSize')->getData();
                $title = $event->getForm()->get('title')->getData();
                $entity = $event->getForm()->getData();
                if ($entity instanceof DatasetSubmission) {
                    if (null !== $size and null !== $hash and null !== $name) {
                        $entity->setDatasetFileColdStorageAttributes($size, $hash, $name, $totalCount, $totalBytes);
                    } else {
                        $entity->clearDatasetFileColdStorageAttributes();
                    }
                    $entity->setTitle(preg_replace("/(\r|\n)/", ' ', $title));
                }
            }
        );
    }

    /**
     * Configures the options for this type.
     *
     * @return void
     */
    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $entity = $this->formEntity;
        $poc = $this->formPoc;

        $resolver->setDefaults([
            'data_class' => DatasetSubmission::class,
            'allow_extra_fields' => true,
            'empty_data' => function (FormInterface $form) use ($entity, $poc) {
                return new DatasetSubmission($entity, $poc);
            },
            'csrf_protection' => false,
        ]);
    }
}

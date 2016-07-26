<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use Pelagos\Entity\Metadata;

/**
 * A form type for creating a Dataset Submission form.
 */
class MdappType extends AbstractType
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
            ->add('validateSchema', CheckboxType::class, array(
                'label' => 'Validate as ISO-19115-2',
                'required' => false,
                'mapped' => false,
            ))
            ->add('acceptMetadata', CheckboxType::class, array(
                'label' => 'Accept Metadata',
                'required' => false,
                'mapped' => false,
            ))
            ->add('overrideDatestamp', CheckboxType::class, array(
                'label' => 'Create/Update gmi:MI_Metadata/gmd:dateStamp with current gco:dateTime',
                'required' => false,
                'mapped' => false,
            ))
            ->add('test1', CheckboxType::class, array(
                'label' => 'Filename matches file identifier (/gmi:MI_Metadata/gmd:fileIdentifier[1]/gco:CharacterString[1]) as UDI-metadata.xml (w/dash instead of colon)',
                'required' => false,
                'mapped' => false,
            ))
            ->add('test2', CheckboxType::class, array(
                'label' => 'UDI matches metadata URL (/gmi:MI_Metadata/gmd:dataSetURI/gco:CharacterString)',
                'required' => false,
                'mapped' => false,
            ))
            ->add('test3', CheckboxType::class, array(
                'label' => 'UDI matches distribution URL (/gmi:MI_Metadata/gmd:distributionInfo[1]/gmd:MD_Distribution[1]/gmd:distributor[1]/gmd:MD_Distributor[1]/gmd:distributorTransferOptions[1]/gmd:MD_DigitalTransferOptions[1]/gmd:onLine[1]/gmd:CI_OnlineResource[1]/gmd:linkage[1]/gmd:URL[1])',
                'required' => false,
                'mapped' => false,
            ))
            ->add('Upload File', FileType::class, array(
                'label' => 'Upload File',
                'required' => false,
                'mapped' => false,
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
            'data_class' => 'Pelagos\Entity\Metadata',
            'allow_extra_fields' => true,
        ));
    }
}

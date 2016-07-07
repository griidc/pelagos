<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A form for retrieving a collection of entities.
 */
class EntityCollectionType extends AbstractType
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
        $collectionName = $options['label'];
        $entityName = $options['data_class']::FRIENDLY_NAME;
        $builder
            ->add('someProperty', TextType::class, array(
                'required' => false,
                'description' => "Only return $collectionName where someProperty=value " .
                                 "(where someProperty is any valid property or sub-property of a $entityName).",
            ))
            ->add('_permission', TextType::class, array(
                'required' => false,
                'description' => "Only return $collectionName for which the current user " .
                                 'has the specified permission (e.g. CAN_EDIT). ',
            ))
            ->add('properties', TextType::class, array(
                'required' => false,
                'description' => "Return these properties for each $entityName. " .
                                 'Sub-properties of related entities can be accessed using dot notation ' .
                                 '(e.g. relatedEntityProperty.subProperty).',
            ))
            ->add('orderBy', TextType::class, array(
                'required' => false,
                'description' => 'Order by these properties. ' .
                                 'The default order is ascending (ASC). ' .
                                 'Can order descending by adding :DESC to the property.',
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
            'data_class' => 'Pelagos\Entity\Entity',
        ));
    }
}

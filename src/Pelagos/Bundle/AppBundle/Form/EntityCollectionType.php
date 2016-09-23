<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A form for retrieving a collection of entities.
 */
class EntityCollectionType extends EntityDescriptionsType
{
    /**
     * This form type is used to *return* a collection.
     */
    const ACTION = 'return';

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
                'description' => $this->getPropertyFilterDescription($collectionName, $entityName),
            ))
            ->add('_permission', TextType::class, array(
                'required' => false,
                'description' => $this->getPermissionDescription($collectionName),
            ))
            ->add('_properties', TextType::class, array(
                'required' => false,
                'description' => $this->getPropertiesDescription($entityName),
            ))
            ->add('_orderBy', TextType::class, array(
                'required' => false,
                'description' => $this->getOrderByDescription(),
            ));
    }
}

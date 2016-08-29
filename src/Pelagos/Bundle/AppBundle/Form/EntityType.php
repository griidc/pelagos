<?php

namespace Pelagos\Bundle\AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * An abstract form for entities.
 */
abstract class EntityType extends AbstractType
{
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

    /**
     * Get the description for the property filter parameter.
     *
     * @param mixed $collectionName The name of the entity collection.
     * @param mixed $entityName     The name of the entity.
     *
     * @return string
     */
    protected function getPropertyFilterDescription($collectionName, $entityName)
    {
        return "Only return $collectionName where someProperty=value " .
               "(where someProperty is any valid property or sub-property of a $entityName).";
    }

    /**
     * Get the description for the permissions parameter.
     *
     * @param mixed $collectionName The name of the entity collection.
     *
     * @return string
     */
    protected function getPermissionDescription($collectionName)
    {
        return "Only return $collectionName for which the current user " .
               'has the specified permission (e.g. CAN_EDIT). ';
    }

    /**
     * Get the description for the properties parameter.
     *
     * @param mixed $entityName The name of the entity.
     *
     * @return string
     */
    protected function getPropertiesDescription($entityName)
    {
        return "Return these properties for each $entityName. " .
               'Sub-properties of related entities can be accessed using dot notation ' .
               '(e.g. relatedEntityProperty.subProperty).';
    }

    /**
     * Get the description for the order by parameter.
     *
     * @return string
     */
    protected function getOrderByDescription()
    {
        return 'Order by these properties. ' .
               'The default order is ascending (ASC). ' .
               'Can order descending by adding :DESC to the property.';
    }
}

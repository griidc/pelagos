<?php

namespace Pelagos;

/**
 * Custom Twig extensions.
 */
class TwigEntityExtensions extends \Twig_Extension
{
    /**
     * Return the name of this extension set.
     *
     * @return string The name of this extension set.
     */
    public function getName()
    {
        return 'Pelagos\Entity';
    }

    /**
     * Return a list of filters.
     *
     * @return array A list of Twig filters.
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'sortBy',
                array(self::class, 'sortBy')
            ),
        );
    }

    /**
     * Sorts an entity by the specified properties.
     *
     * @param mixed $entityList The list of entities to sort.
     * @param array $properties The properties to sort by.
     *
     * @return array The sorted list of entities.
     */
    public static function sortBy($entityList, array $properties)
    {
        if (gettype($entityList) == 'object' and is_a($entityList, 'Doctrine\Common\Collections\Collection')) {
            // If the entity list is a collection, get it as an array.
            $entityList = $entityList->toArray();
        }
        $collator = collator_create('en_US.UTF-8');
        usort(
            $entityList,
            function ($a, $b) use ($properties, $collator) {
                while (count($properties) > 0) {
                    // For each sort criteria, reset 'a' and 'b' entity back to original.
                    $aEntity = $a;
                    $bEntity = $b;
                    // Pull a property descriptor of the front of the properties array.
                    $propertyDescriptor = array_shift($properties);
                    // Break property descriptor into pieces.
                    $propertyChain = explode('.', $propertyDescriptor);
                    // Go through each property in the chain.
                    while (count($propertyChain) > 0) {
                        // Pull a property off the front of the chain.
                        $property = array_shift($propertyChain);
                        // Make sure the the property exists on both sides.
                        if (!$aEntity->propertyExists($property) or !$bEntity->propertyExists($property)) {
                            // If $property doesn't exist on either side, we can't sort.
                            return 0;
                        }
                        // Get the properties list from both sides.
                        $aProperties = $aEntity->getProperties();
                        $bProperties = $bEntity->getProperties();
                        // Make sure the property has a getter on both sides.
                        if (!array_key_exists($property, $aProperties) or
                            !array_key_exists($property, $bProperties) or
                            !array_key_exists('getter', $aProperties[$property]) or
                            !array_key_exists('getter', $bProperties[$property])) {
                            // If $property doesn't have a getter specified on either side, we can't sort.
                            return 0;
                        }
                        // Get values for the property from both sides via the getter.
                        $aVal = $aEntity->$aProperties[$property]['getter']();
                        $bVal = $bEntity->$bProperties[$property]['getter']();
                        // If the property value from 'a' is a reference to another entity.
                        if (gettype($aVal) == 'object' and $aVal instanceof \Pelagos\Entity\Entity) {
                            // Put it in to $aEntity to allow further processing.
                            $aEntity = $aVal;
                            // Set $aVal to the entity's id in case we are comparing whole entities.
                            $aVal = $aVal->getId();
                        }
                        // If the property value from 'b' is a reference to another entity.
                        if (gettype($bVal) == 'object' and $bVal instanceof \Pelagos\Entity\Entity) {
                            // Put it in to $bEntity to allow further processing.
                            $bEntity = $bVal;
                            // Set $bVal to the entity's id in case we are comparing whole entities.
                            $bVal = $bVal->getId();
                        }
                    }

                    if (gettype($aVal) == 'string' and gettype($bVal) == 'string') {
                        // It the values on both sides are strings, use a string comparison.
                        $cmp = collator_compare($collator, $aVal, $bVal);
                        if ($cmp != 0) {
                            return $cmp;
                        }
                    } else {
                        // Otherwise use standard comparison operators.
                        if ($aVal < $bVal) {
                            return -1;
                        }
                        if ($aVal > $bVal) {
                            return 1;
                        }
                    }
                }
                return 0;
            }
        );
        return $entityList;
    }
}

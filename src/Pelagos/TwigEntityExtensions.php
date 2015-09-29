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
            'sortBy' => new \Twig_Filter_Method(
                $this,
                'sortBy'
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
    public function sortBy($entityList, array $properties)
    {
        if (gettype($entityList) == 'object' and is_a($entityList, 'Doctrine\Common\Collections\Collection')) {
            // If the entity list is a collection, get it as an array.
            $entityList = $entityList->toArray();
        }
        usort(
            $entityList,
            function ($a, $b) use ($properties) {
                while (count($properties) > 0) {
                    $property = array_shift($properties);
                    if (!$a->propertyExists($property) or !$b->propertyExists($property)) {
                        // If $property doesn't exist on either side, we can't sort.
                        return 0;
                    }
                    $aProperties = $a->getProperties();
                    $bProperties = $b->getProperties();
                    if (!array_key_exists($property, $aProperties) or
                        !array_key_exists($property, $bProperties) or
                        !array_key_exists('getter', $aProperties[$property]) or
                        !array_key_exists('getter', $bProperties[$property])) {
                        // If $property doesn't have a getter specified on either side, we can't sort.
                        return 0;
                    }
                    $aVal = $a->$aProperties[$property]['getter']();
                    $bVal = $b->$bProperties[$property]['getter']();
                    if (gettype($aVal) == 'string' and gettype($bVal) == 'string') {
                        // It the values on both sides are strings, use a string comparison.
                        $cmp = strcmp($aVal, $bVal);
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

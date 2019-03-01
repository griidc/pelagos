<?php
namespace Pelagos\Util;

/**
 * This is a utility class for general purpose utilities.
 */
class PelagosUtilities
{
    /**
     * This function returns a boolean true when all items are null, or all not null.
     *
     * The function returns false when at least one item is not null but not all are not null.
     *
     * @param array $items The array of items that potentially have a null subset.
     *
     * @return boolean
     */
    public static function nullOrNone(array $items)
    {
        $nullCount = 0;
        foreach ($items as $item) {
            if (null === $item) {
                $nullCount++;
            }
        }
        return ($nullCount === 0 or $nullCount === count($items));
    }
}

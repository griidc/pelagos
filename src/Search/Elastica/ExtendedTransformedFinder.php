<?php

namespace App\Search\Elastica;

use FOS\ElasticaBundle\Finder\TransformedFinder;

/**
 * Extended Transformed Finder to expose the SearchableInterface.
 */
class ExtendedTransformedFinder extends TransformedFinder
{
    /**
     * Return the SearchableInterface which is used to perform searches.
     *
     * @return \Elastica\SearchableInterface
     */
    public function getSearch()
    {
        return $this->searchable;
    }
}

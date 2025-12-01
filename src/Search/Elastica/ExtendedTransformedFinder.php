<?php

namespace App\Search\Elastica;

use FOS\ElasticaBundle\Finder\TransformedFinder;

/**
 * Extended Transformed Finder to expose the SearchableInterface.
 */
class ExtendedTransformedFinder extends TransformedFinder
{
    /**
     * @return \Elastica\SearchableInterface
     */
    public function getSearch()
    {
        return $this->searchable;
    }

}

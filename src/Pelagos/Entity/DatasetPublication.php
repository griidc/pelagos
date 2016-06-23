<?php

namespace Pelagos\Entity;

use Pelagos\Entity\Publication;
use Pelagos\Entity\Dataset;

/**
 * This Entity contains a link between Publications and Datasets.
 */
class DatasetPublication extends Entity
{
    /**
     * A Pelagos Publication entity.
     *
     * @var Publication
     */
    protected $publication;

    /**
     * A Pelagos Datasent entity.
     *
     * @var Dataset
     */
    protected $dataset;
}

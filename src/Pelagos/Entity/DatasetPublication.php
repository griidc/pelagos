<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\PublicationCitation;

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

<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\Publication;
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
     *
     * @ORM\ManyToOne(targetEntity="Publication", inversedBy="datasetPublications")
     */
    protected $publication;

    /**
     * A Pelagos Datasent entity.
     *
     * @var Dataset
     */
    protected $dataset;

    /**
     * Getter for Publication.
     *
     * @return Publication
     */
    public function getPublication()
    {
        return $this->publication();
    }

    /**
     * Getter for Dataset.
     *
     * @return Dataset
     */
    public function getDataset()
    {
        return $this->dataset();
    }
}

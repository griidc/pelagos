<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * This Entity contains a link between Publications and Datasets.
 *
 *
 */
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'uniq_dataset_publication', columns: ['publication_id', 'dataset_id'])]
#[ORM\Entity]
class DatasetPublication
{
    use EntityTrait;
    use EntityIdTrait;
    use EntityDateTimeTrait;

    /**
     * A Pelagos Publication entity.
     *
     * @var Publication
     */
    #[ORM\ManyToOne(targetEntity: 'Publication', inversedBy: 'datasetPublications')]
    #[Serializer\Groups(['publications'])]
    protected $publication;

    /**
     * A Pelagos Datasent entity.
     *
     * @var Dataset
     */
    #[ORM\ManyToOne(targetEntity: 'Dataset', inversedBy: 'datasetPublications')]
    protected $dataset;

    /**
     * Class constructor.
     *
     * @param Publication $publication A Pelagos Publication.
     * @param Dataset     $dataset     A Pelagos Dataset.
     */
    public function __construct(Publication $publication, Dataset $dataset)
    {
        $this->publication = $publication;
        $this->dataset = $dataset;
    }

    /**
     * Getter for Publication.
     *
     * @return Publication
     */
    public function getPublication()
    {
        return $this->publication;
    }

    /**
     * Getter for Dataset.
     *
     * @return Dataset
     */
    public function getDataset()
    {
        return $this->dataset;
    }
}

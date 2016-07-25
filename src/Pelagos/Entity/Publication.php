<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Pelagos\HTTPStatus;

/**
 * This class generates a Publication object,* which holds the DOI an a Citation object.
 *
 * @ORM\Entity
 */
class Publication extends Entity
{
    /**
     * Friendly name for Publication Entity.
     */
    const FRIENDLY_NAME = 'Publication';

    /**
     * DOI.
     *
     * @var string
     *
     * @ORM\Column(type="citext")
     */
    protected $doi;

    /**
     * Citations for this Publication.
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="PublicationCitation", mappedBy="publication")
     */
    protected $citations;

    /**
     * Collection of DatasetPublication.
     *
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="DatasetPublication", mappedBy="publication")
     */
    protected $datasetPublications;

    /**
     * Publication Constructor.
     *
     * @param string $doi DOI.
     */
    public function __construct($doi)
    {
        $this->doi = $doi;
    }

    /**
     * Getter for doi.
     *
     * @return string
     */
    public function getDoi()
    {
        return $this->doi;
    }

    /**
     * Sets the Citation object.
     *
     * @param Collection $citations A collection of Pelagos Citations.
     *
     * @return void
     */
    public function setCitations(Collection $citations)
    {
        $this->citations = $citations;
    }

    /**
     * Gets the Citation object.
     *
     * @return Citation A publication citation.
     */
    public function getCitations()
    {
        return $this->citations;
    }
}

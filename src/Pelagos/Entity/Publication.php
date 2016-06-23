<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Pelagos\Entity\PublicationCitation;
use Pelagos\HTTPStatus;

/**
 * This class generates a Publication object,* which holds the DOI an a Citation object.
 *
 * @ORM\Entity
 */
class Publication extends Entity
{
    /**
     * DOI.
     *
     * @var string
     *
     * @ORM\Column(type="citext")
     */
    protected $doi;

    /**
     * Citation.
     *
     * @var Citation
     *
     * @ORM\OneToMany(targetEntity="PublicationCitation", mappedBy="publication")
     */
    protected $citation;

    /**
     * Collection of DatasetPublication.
     *
     * @var Collection
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
     * Sets the Citation object.
     *
     * @param PublicationCitation $citation A Pelagos Citation.
     *
     * @return void
     */
    public function setCitation(PublicationCitation $citation)
    {
        $this->citation = $citation;
    }

    /**
     * Gets the Citation object.
     *
     * @return Citation A publication citation.
     */
    public function getCitation()
    {
        return $this->citation;
    }

    /**
     * Return this class as JSON.
     *
     * @return JSON
     */
    public function asJSON()
    {
        return json_encode(
            array(
                'doi' => $this->doi,
                'citation' => $this->citation,
            ),
            JSON_UNESCAPED_SLASHES
        );
    }
}

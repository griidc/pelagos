<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @var $doi string Contains the DOI.
     *
     * @ORM\Column(type="citext")
     */
    protected $doi;

    /**
     * Citation.
     *
     * @var Citation $citation object Contains the Citation Object.
     *
     * @ORM\OneToMany(targetEntity="PublicationCitation", mappedBy="publication")
     */
    protected $citation;

    /**
     * Publication Constructor.
     *
     * @param string $doi DOI.
     *
     * @return void
     */
    public function __construct($doi)
    {
        $this->doi = $doi;
    }


    /**
     * Sets the Citation object.
     *
     * @param PublicationCitation $citation A Pelagos Citation.
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

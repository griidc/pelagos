<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This class holds a Citation object.
 *
 * @ORM\Entity
 */
class PublicationCitation extends Entity
{

    /**
     * A class constant for citation style.
     */
    const CITATION_STYLE_APA = 'apa';

    /**
     * Citation Text.
     *
     * @var $citationText string
     *
     * @ORM\Column(type="citext")
     */
    private $citationText;

    /**
     * Citation Style.
     *
     * @var $style string
     *
     * @ORM\Column(type="citext")
     */
    private $style;

    /**
     * Citation Locale.
     *
     * @var $locale string
     *
     * @ORM\Column(type="citext")
     */
    private $locale;

    /**
     * The Publication this PublicationCitation is about.
     *
     * @var Publication $publication
     *
     * @ORM\ManyToOne(targetEntity="Publication", inversedBy="citations")
     */
    protected $publication;

    /**
     * Citation Constructor.
     *
     * Will create a Citation Object from given parameters.
     *
     * @param string $citationText Citation Text.
     * @param string $style        Citation Style commonly APA.
     * @param string $locale       Citation Text Locale commonly utf-8.
     */
    public function __construct(
        string $citationText = null,
        string $style = null,
        string $locale = null
    ) {
        $this->citationText = $citationText;
        $this->style = $style;
        $this->locale = $locale;
    }

    /**
     * Setter for Publication.
     *
     * @param Publication $publication A Pelagos Publication.
     *
     * @return void
     */
    public function setPublication(Publication $publication)
    {
        $this->publication = $publication;
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
     * Getter for citationtext.
     *
     * @return string
     */
    public function getCitationText()
    {
        return $this->citationText;
    }
}

<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This class holds a Citation object.
 *
 * @ORM\Entity
 */
class PublicationCitation extends Entity
{
    /**
     * Citation Text.
     *
     * @var $text string
     *
     * @ORM\Column(type="citext")
     */
    private $text;

    /**
     * Citation Style.
     *
     * @var $style string
     *
     * @ORM\Column(type="citext")
     */
    private $style;

    /**
     * Ciation Locale.
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
     * @ORM\ManyToOne(targetEntity="Publication", inversedBy="citation")
     */
    protected $publication;

    /**
     * Citation Constructor.
     *
     * Will create a Citation Object from given parameters.
     *
     * @param string    $id        Citation ID, can be DOI or UDI.
     * @param string    $text      Citation Text.
     * @param string    $style     Citation Style commonly APA.
     * @param string    $locale    Citation Text Locale commonly utf-8.
     */
    public function __construct(
        $id,
        $text = null,
        $style = null,
        $locale = null
    ) {
        $this->id = $id;
        $this->text = $text;
        $this->style = $style;
        $this->locale = $locale;
    }

    /**
     * Returns the Citation Object as an array.
     *
     * @return array
     */
    public function asArray()
    {
        return array(
            'id' => $this->id,
            'text' => $this->text,
            'style' => $this->style,
            'locale' => $this->locale,
            'timestamp' => $this->timestamp->format('c'),
        );
    }

    /**
     * Return the Citation Object as JSON.
     *
     * @return JSON
     */
    public function asJSON()
    {
        return json_encode($this->asArray(), JSON_UNESCAPED_SLASHES);
    }
}

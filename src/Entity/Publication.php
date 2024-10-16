<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;
use Pelagos\HTTPStatus;

/**
 * This class generates a Publication object,* which holds the DOI an a Citation object.
 */
#[ORM\Entity]
class Publication extends Entity
{
    /**
     * Friendly name for Publication Entity.
     */
    const FRIENDLY_NAME = 'Publication';

    /**
     * A class constant for citation style, now hardcoded.
     */
    const CITATION_STYLE_APA = 'apa';

    /**
     * Class constant for locale, now hardcoded.
     */
    const CITATION_LOCALE = 'en-US';

    /**
     * Citation Text.
     *
     * @var $citationText string
     */
    #[ORM\Column(type: 'citext')]
    #[Serializer\Groups(['citation'])]
    private $citationText;

    /**
     * DOI.
     *
     * @var string
     */
    #[ORM\Column(type: 'citext')]
    #[Serializer\Groups(['citation'])]
    protected $doi;

    /**
     * Collection of DatasetPublication.
     *
     * @var Collection
     *
     */
    #[ORM\OneToMany(targetEntity: 'DatasetPublication', mappedBy: 'publication')]
    #[Serializer\MaxDepth(1)]
    protected $datasetPublications;

    /**
     * Publication Constructor.
     *
     * @param string $doi DOI.
     */
    public function __construct(string $doi)
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
     * Sets the Citation text.
     *
     * @param string $citationText A citation as an APA formatted UTF-8 locale string.
     *
     * @return void
     */
    public function setCitationText(string $citationText)
    {
        $this->citationText = $citationText;
    }

    /**
     * Gets the Citation text.
     *
     * @return string The citation text.
     */
    public function getCitationText()
    {
        return $this->citationText;
    }
}

<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DOI Entity class.
 *
 * @ORM\Entity
 */
class DOI extends Entity
{

    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'DOI';

    /**
     * Status value for a DOI Request that has been submitted for approval.
     */
    const STATUS_SUBMITTED = 0;

    /**
     * Status value for a DOI Request that is approved.
     */
    const STATUS_APPROVED = 1;

    /**
     * Status value for a DOI Request, when the DOI has been generated and issued.
     */
    const STATUS_ISSUED = 2;

    /**
     * The DOI for this Dataset.
     *
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $doi;

    /**
     * The status of this DOI.
     *
     * @var integer
     *
     * @see STATUS_* constants.
     *
     * @ORM\Column(type="smallint")
     */
    protected $status = self::STATUS_SUBMITTED;

    /**
     * The URL for this DOI.
     *
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(
     *     message="Website URL is required"
     * )
     * @Assert\NoAngleBrackets(
     *     message="Website URL cannot contain angle brackets (< or >)"
     * )
     */
    protected $url;

    /**
     * The DOI Responsible Party (Creator) for this DOI.
     *
     * Since creator is already used in the base entity,
     * this attribute is called responsibleParty,
     * but is really the DOI creator.
     *
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(
     *     message="Creator is required"
     * )
     */
    protected $responsibleParty;

    /**
     * The title for this DOI.
     *
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(
     *     message="Title is required"
     * )
     */
    protected $title;

    /**
     * The publisher for this DOI.
     *
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank(
     *     message="Publisher is required"
     * )
     */
    protected $publisher;

    /**
     * Sets the DOI.
     *
     * @param string $doi The DOI.
     *
     * @return void
     */
    public function setDoi($doi)
    {
        $this->doi = $doi;
    }

    /**
     * Gets the DOI.
     *
     * @return string The DOI.
     */
    public function getDoi()
    {
        return $this->doi;
    }

    /**
    * Set the DOI request status.
    *
    * @see STATUS_* constants.
    *
    * @param integer $status The status of the DOI request.
    *
    * @return void
    */
    protected function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Returns the status of this DIF.
     *
     * @see STATUS_* constants.
     *
     * @return integer The status of this DIF.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Setter for url.
     *
     * @param string $url The URL for this DOI.
     *
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Getter for url.
     *
     * @return string The URL for this DOI.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Setter for Responsible Party (creator).
     *
     * @param string $responsibleParty The Responsible Party (Creator) of the DOI.
     *
     * @return void
     */
    public function setResponsibleParty($responsibleParty)
    {
        $this->creator = $responsibleParty;
    }

    /**
     * Getter for Responsible Party (creator).
     *
     * @return string The Responsible Party (Creator) of the DOI.
     */
    public function getResponsibleParty()
    {
        return $this->responsibleParty;
    }

    /**
     * Sets the title for this DOI.
     *
     * @param string $title The title for this DOI.
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Gets the title for this DOI.
     *
     * @return string The title for this DOI.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the publisher for this DOI.
     *
     * @param string $publisher The publisher for this DOI.
     *
     * @return void
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * Gets the publisher for this DOI.
     *
     * @return string The publisher for this DOI.
     */
    public function getPublisher()
    {
        return $this->publisher;
    }
}

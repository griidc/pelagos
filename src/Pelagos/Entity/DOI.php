<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * Status value for a DOI that is publicly available.
     */
    const STATUS_PUBLIC = 'public';

    /**
     * Status value for a DOI that is reserved and not publicly available.
     */
    const STATUS_RESERVED = 'reserved';

    /**
     * Status value for a DOI that is publicly visible, but does not resolve.
     */
    const STATUS_UNAVAILABLE = 'unavailable';

    /**
     * The DOI identifier.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected $doi;

    /**
     * The status of this DOI.
     *
     * @var integer
     *
     * @see STATUS_* constants.
     *
     * @ORM\Column(type="text")
     */
    protected $status;

    /**
     * The date the DOI is made public.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    protected $publicDate;

    /**
     * Constructor.
     *
     * Sets to DOI identifier for the DOI entity.
     */
    public function __construct($doi)
    {
        $this->setDoi($doi);
        // Initial state for issue is reserved.
        $this->setStatus(self::STATUS_RESERVED);
    }

    /**
     * Sets the DOI.
     *
     * @param string $doi The DOI.
     *
     * @return void
     */
    protected function setDoi($doi)
    {
        // If the identifier contains doi:, remove it.
        $doi = preg_replace('/^(?:doi:)?(10.\S+)/', '$1', $doi);
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
     * @param string $status The status of the DOI request.
     *
     * @see STATUS_* constants.
     *
     * @return void
     */
    protected function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Returns the status of this DOI Request.
     *
     * @see STATUS_* constants.
     *
     * @return string The status of this DOI Request.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Gets the public date of the DOI.
     *
     * @return \DateTime The public date for this DOI.
     */
    public function getPublicDate()
    {
        return $this->publicDate;
    }

    /**
     * Sets the public date of the DOI.
     *
     * @param \DateTime $publicDate The public date for this DOI.
     *
     * @return void
     */
    public function setPublicDate(\DateTime $publicDate)
    {
        $this->publicDate = $publicDate;
    }
}

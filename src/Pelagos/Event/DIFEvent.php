<?php

namespace Pelagos\Event;

use Symfony\Component\EventDispatcher\Event;

use Pelagos\Entity\DIF;

/**
 * Class for DIF events.
 */
class DIFEvent extends Event
{
    /**
     * The DIF this event is for.
     *
     * @var DIF
     */
    protected $dif;

    /**
     * Constructor.
     *
     * @param DIF $dif The DIF this event is for.
     */
    public function __construct(DIF $dif)
    {
        $this->dif = $dif;
    }

    /**
     * Gets the DIF this event is for.
     *
     * @return DIF The DIF this event is for.
     */
    public function getDIF()
    {
        return $this->dif;
    }
}

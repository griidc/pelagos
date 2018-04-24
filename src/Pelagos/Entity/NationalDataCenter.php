<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * National Data Center to Data Center dataset contact association entity class.
 *
 * @ORM\Entity
 */
class NationalDataCenter extends DataCenter
{
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'National Data Center';

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->nationalCenter = true;
    }
}

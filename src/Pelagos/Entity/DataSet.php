<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

use Pelagos\Entity\DIF;

/**
 * Dataset Entity class.
 *
 * @ORM\Entity
 */
class DataSet extends Entity
{
    
    /**
     * A friendly name for this type of entity.
     */
    const FRIENDLY_NAME = 'DIF';
    
    /**
     * The UDI for this DataSet.
     *
     * @var string
     *
     * @ORM\Column
     */
    protected $udi;
    
    /**
     * The DIF this DataSet is attached to.
     *
     * @var DIF
     *
     * @ORM\OneToOne(targetEntity="DIF")
     */
    protected $dif;
        
    /**
     * Sets the UDI for this DataSet.
     *
     * @param string $udi The UDI for this DataSet.
     *
     * @return void
     */
    public function setUdi($udi)
    {
        $this->udi = $udi;
    }
    
    /**
     * Gets the UDI for this DataSet.
     *
     * @return string The UDI for this DataSet.
     */
    public function getUdi()
    {
        return $this->udi;
    }
    
    /**
     * Sets the DIF for this DataSet.
     *
     * @param DIF $dif The DIF for this DataSet.
     *
     * @return void
     */
    public function setDif(DIF $dif)
    {
        $this->dif = $dif;
    }
    
    /**
     * Gets the DIF for this DataSet.
     *
     * @return DIF The DIF for this DataSet.
     */
    public function getDif()
    {
        return $this->dif;
    }
}

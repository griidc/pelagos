<?php
/**
 * Metadata
 * Texas A&M Corpus Christi
 * Harte Research Institute
 * Gulf (of Mexico) Research Initiative Information Data Cooperative
 * GRIIDC
 */

namespace Metadata;


use Persistence\I_Persistable;

class Metadata implements I_Persistable
{


    /** @var int  unique identifier generated outside probably by storage engine (PGSQL ?) */
    private $registryId = null;
    /** @var string  sir name */
    private $metadataXml = null;
    /** @var string given name */
    private $geometry = null;
    /** @var string geometry extent description */
    private $extentDescription = null;

    /**
     * @param string $metadataXml
     * @param string $geometry
     * @param string $emailAddr
     * Instantiate a new Person and initialize with parameters provided
     */
    public function __construct($metadataXml = null, $geometry = null, $extentDescription = null, $registryId = null)
    {
        $this->metadataXml = $metadataXml;
        $this->geometry = $geometry;
        $this->extentDescription = $extentDescription;
        if ($registryId != null) {
            $this->registryId = $registryId;
        }
    }

    /**
     * Accessor for the metadataXml property
     * Returns the metadataXml
     * @return string
     */
    public function getMetadataXml()
    {
        return $this->metadataXml;
    }

    /**
     * Assign the metadataXml attribute
     * @param $metadataXml
     */
    public function setMetadataXml($metadataXml)
    {
        $this->metadataXml = $metadataXml;
    }

    /**
     * Accessor for the geometry property.
     * Returns the geometry
     * @return string
     */
    public function getGeometry()
    {
        return $this->geometry;
    }

    /**
     * Assign the geometry property
     * @param $geometry
     */
    public function setGeometry($geometry)
    {
        $this->geometry = $geometry;
    }

    /**
     * Access for the geometry extent description property
     * Returns the geometry extent description as a string
     * @return string
     */
    public function getExtentDescription()
    {
        return $this->extentDescription;
    }

    /**
     * Assign the geometry extent description property
     * @param $extentDescription
     */
    public function setExtentDescription($extentDescription)
    {
        $this->extentDescription = $extentDescription;
    }

    /**
     * Accessor for the registryId.
     * Return the unique identifier for this instance of Person
     * @return int
     */
    public function getRegistryId()
    {
        return $this->registryId;
    }

    /**
     * Set the value of the registryIdproperty.
     * This is required because the registryIdis provided by a
     * mechanism outside this class;
     * @param $uniqueId
     */
    public function setRegistryId($uniqueRegistryId)
    {
        $this->registryId = $uniqueRegistryId;
    }

    /**
     * functionality imposed by I_Persistable
     */
    public static function getClassName()
    {
        return __CLASS__;
    }

    /**
     * returm the unique identifier property
     * of this object.
     * @return int
     */
    public function getKey()
    {
        return $this->getRegistryId();
    }

    /**
     * assign the unique identifier for this object
     * @param $k
     */
    public function setKey($k)
    {
        $this->setRegistryId($k);
    }

    /**
     * Return a string representation of the instance
     * for use in display etc.
     * @return string
     */
    public function __toString()
    {
        $NA = "N/A";

        $idString = $NA;
        if ($this->getRegistryId() != null) {
            $idString = $this->getRegistryId();
        }
        $compressedXml = $NA;
        if ($this->getMetadataXml() != null) {
            $compressedXml = $ro = preg_replace('/\s+/', ' ',$this->getMetadataXml());
        }
        $geomString = $NA;
        if ($this->getGeometry() != null) {
            $geomString = $this->getGeometry();;
        }
        $extent = $NA;
        if ($this->getExtentDescription() != null) {
            $extent = $this->getExtentDescription();;
        }

        return "Metadata: ID: " . $idString . ", " .
               "\nXML: ".$compressedXml . ", " .
               "\nGeometry: ".$geomString . " - " .
               "\nExtent Description: ".$extent;
    }

    /**
     * If the other object is the same type as this type
     * has identical values as this instance
     * return true.
     * Otherwise return false;
     * @param $other
     */
    public function isEqual($other)
    {
        if (is_object($other) == false) {
            return false;
        }

        if (strcmp(get_class($other), $this->getClassName()) == 0
            && strcmp($this->getMetadataXml(), $other->getMetadataXml()) == 0
            && strcmp($this->getGeometry(), $other->getGeometry()) == 0
            && strcmp($this->getExtentDescription(), $other->getExtentDescription()) == 0
        ) {

            return true;

        }
        return false;
    }

    /**
     * Compare the attributes that are set in the target to the
     * corresponding attributes in this object.
     * Only compare those attributes set in the target,
     *
     * If this object matches all the fields that are set in the target
     * then return true;
     * else false
     * @param I_Persistable $target
     */
    public function matchesTarget(I_Persistable $target)
    {
        if (is_object($target) == false) {
            return false;
        }

        if (strcmp(get_class($target), $this->getClassName()) != 0) return false;
        if ($target->getMetadataXml() != null && strcmp($this->getMetadataXml(), $target->getMetadataXml()) != 0) return false;
        if ($target->getGeometry() != null && strcmp($this->getGeometry(), $target->getGeometry()) != 0) return false;
        if ($target->getExtentDescription() != null && strcmp($this->getExtentDescription(), $target->getExtentDescription()) != 0) return false;
        if ($target->getRegistryId() != null && strcmp($this->getRegistryId(), $target->getRegistryId()) != 0) return false;
        return true;
    }
}
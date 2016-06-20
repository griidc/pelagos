<?php

namespace Pelagos\Entity;

use Doctrine\ORM\Mapping as ORM;

class Metadata extends Entity {
    /**
     * The Dataset this DIF identifies.
     *
     * @var Dataset
     *
     * @ORM\OneToOne(targetEntity="Dataset", mappedBy="metadata", cascade={"persist"})
     */
    protected $dataset;

    /**
     * XML of the Metadata.
     *
     * @var string
     *
     * @ORM\Column(type="xml")
     */
    protected $xml;

    /**
     * Geometry of the Metadata.
     *
     * @var string
     *
     * @ORM\Column(type="geometry", options={"geometry_type"="Geometry", "srid"=4326)
     */
    protected $geometry;

    /**
     * Description of the extent in the metadata.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $extentDescription;

    /**
     * Title of the Metadata.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * Abstract of the Metadata.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $abstract;

    /**
     * Start date of the dataset in the metadata.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $beginPosition;

    /**
     * End date of the dataset in the metadata.
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $endPosition;

    /**
     * The file format of the dataset in the metadata.
     *
     * @var PROPTYPE
     *
     * @ORM\Column(type="string")
     */
    protected $fileFormat;

    /**
     * The purpose of the dataset in the metadata.
     *
     * @var PROPTYPE
     *
     * @ORM\Column(type="string")
     */
    protected $purpose;

    /**
     * An array of theme keywords for the Metadata.
     *
     * @var array
     *
     * @ORM\Column(type="text_array")
     */
    protected $themeKeywords;

    /**
     * Contructor.
     *
     * @param Dataset $dataset Dataset this metadata is for.
     * @param string  $xml     XML for this metadata.
     */
    public __construct(Dataset $dataset, $xml)
    {
        $this->setDataset($dataset);
        $this->setXml($xml);
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getDataset()
    {
        return $this->dataset;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @param ARGTYPE $dataset ARGDESCRIPTION
     */
    public function setDataset($dataset)
    {
        $this->dataset = $dataset;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @param ARGTYPE $xml ARGDESCRIPTION
     */
    public function setXml($xml)
    {
        $this->xml = $xml;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getGeometry() {
        return $this->geometry;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @param ARGTYPE $geometry ARGDESCRIPTION
     */
    public function setGeometry($geometry) {
        $this->geometry = $geometry;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getExtentDescription() {
        return $this->extentDescription;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @param ARGTYPE $extentDescription ARGDESCRIPTION
     */
    public function setExtentDescription($extentDescription) {
        $this->extentDescription = $extentDescription;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @param ARGTYPE $title ARGDESCRIPTION
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getAbstract() {
        return $this->abstract;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @param ARGTYPE $abstract ARGDESCRIPTION
     */
    public function setAbstract($abstract) {
        $this->abstract = $abstract;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getBeginPosition() {
        return $this->beginPosition;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @param ARGTYPE $beginPosition ARGDESCRIPTION
     */
    public function setBeginPosition($beginPosition) {
        $this->beginPosition = $beginPosition;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getEndPosition() {
        return $this->endPosition;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @param ARGTYPE $endPosition ARGDESCRIPTION
     */
    public function setEndPosition($endPosition) {
        $this->endPosition = $endPosition;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFileFormat() {
        return $this->fileFormat;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @param ARGTYPE $fileFormat ARGDESCRIPTION
     */
    public function setFileFormat($fileFormat) {
        $this->fileFormat = $fileFormat;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getPurpose() {
        return $this->purpose;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @param ARGTYPE $purpose ARGDESCRIPTION
     */
    public function setPurpose($purpose) {
        $this->purpose = $purpose;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getThemeKeywords() {
        return $this->themeKeywords;
    }

    /**
     * METHODDESCRIPTION
     *
     * @access public
     * @param ARGTYPE $themeKeywords ARGDESCRIPTION
     */
    public function setThemeKeywords($themeKeywords) {
        $this->themeKeywords = $themeKeywords;
    }

    /**
     * Sets the Metadata title from the Metadata XML.
     *
     * @return void
     */
    private function setPropertiesFromXml()
    {
        if (null == $this->xml) {
            return;
        }

        $titles = $this->xml->xpath(
            '/gmi:MI_Metadata/gmd:identificationInfo/gmd:MD_DataIdentification/gmd:citation/gmd:CI_Citation/gmd:title/gco:CharacterString/text()'
        );

        if (count($titles) == 1) {
            $this->setTitle($titles[0]);
        }

        $titles = $this->xml->xpath(
            
	);

        if (count($titles) == 1) {
            $this->setTitle($titles[0]);
        }
    }
}

<?php

namespace Pelagos\Entity;

/**
 * DIF Entity class
 */
class DIF extends Entity
{
    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $status;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $researchGroup;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $title;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $primaryPointOfContact;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $secondaryPointOfContact;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $abstract;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $fieldOfStudyEcologicalBiological;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $fieldOfStudyPhysicalOceanography;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $fieldOfStudyAtmospheric;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $fieldOfStudyChemical;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $fieldOfStudyHumanHealth;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $fieldOfStudySocialCulturalPolitical;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $fieldOfStudyEconomics;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $fieldOfStudyOther;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $dataSize;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $variablesObserved;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $collectionMethodFieldSampling;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $collectionMethodSimulatedGenerated;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $collectionMethodLaboratory;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $collectionMethodLiteratureBased;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $collectionMethodRemoteSensing;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $collectionMethodOther;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $estimatedStartDate;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $estimatedEndDate;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $spatialDescription;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $spatialGeometry;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $collectionMethod;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $nationalDataArchiveNODC;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $nationalDataArchiveUSEPAStoret;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $nationalDataArchiveGBIF;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $nationalDataArchiveNCBI;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $nationalDataArchiveDataGov;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $nationalDataArchiveOther;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $ethicalIssues;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $ethicalIssuesExplanation;

    /**
     * PROPDESCRIPTION
     * 
     * @access protected
     * @var PROPTYPE
     */
    protected $remarks;

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $status ARGDESCRIPTION
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getResearchGroup()
    {
        return $this->researchGroup;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $researchGroup ARGDESCRIPTION
     */
    public function setResearchGroup($researchGroup)
    {
        $this->researchGroup = $researchGroup;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $title ARGDESCRIPTION
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getPrimaryPointOfContact()
    {
        return $this->primaryPointOfContact;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $primaryPointOfContact ARGDESCRIPTION
     */
    public function setPrimaryPointOfContact($primaryPointOfContact)
    {
        $this->primaryPointOfContact = $primaryPointOfContact;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getSecondaryPointOfContact()
    {
        return $this->secondaryPointOfContact;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $secondaryPointOfContact ARGDESCRIPTION
     */
    public function setSecondaryPointOfContact($secondaryPointOfContact)
    {
        $this->secondaryPointOfContact = $secondaryPointOfContact;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $abstract ARGDESCRIPTION
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyEcologicalBiological()
    {
        return $this->fieldOfStudyEcologicalBiological;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $fieldOfStudyEcologicalBiological ARGDESCRIPTION
     */
    public function setFieldOfStudyEcologicalBiological($fieldOfStudyEcologicalBiological)
    {
        $this->fieldOfStudyEcologicalBiological = $fieldOfStudyEcologicalBiological;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyPhysicalOceanography()
    {
        return $this->fieldOfStudyPhysicalOceanography;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $fieldOfStudyPhysicalOceanography ARGDESCRIPTION
     */
    public function setFieldOfStudyPhysicalOceanography($fieldOfStudyPhysicalOceanography)
    {
        $this->fieldOfStudyPhysicalOceanography = $fieldOfStudyPhysicalOceanography;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyAtmospheric()
    {
        return $this->fieldOfStudyAtmospheric;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $fieldOfStudyAtmospheric ARGDESCRIPTION
     */
    public function setFieldOfStudyAtmospheric($fieldOfStudyAtmospheric)
    {
        $this->fieldOfStudyAtmospheric = $fieldOfStudyAtmospheric;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyChemical()
    {
        return $this->fieldOfStudyChemical;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $fieldOfStudyChemical ARGDESCRIPTION
     */
    public function setFieldOfStudyChemical($fieldOfStudyChemical)
    {
        $this->fieldOfStudyChemical = $fieldOfStudyChemical;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyHumanHealth()
    {
        return $this->fieldOfStudyHumanHealth;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $fieldOfStudyHumanHealth ARGDESCRIPTION
     */
    public function setFieldOfStudyHumanHealth($fieldOfStudyHumanHealth)
    {
        $this->fieldOfStudyHumanHealth = $fieldOfStudyHumanHealth;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudySocialCulturalPolitical()
    {
        return $this->fieldOfStudySocialCulturalPolitical;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $fieldOfStudySocialCulturalPolitical ARGDESCRIPTION
     */
    public function setFieldOfStudySocialCulturalPolitical($fieldOfStudySocialCulturalPolitical)
    {
        $this->fieldOfStudySocialCulturalPolitical = $fieldOfStudySocialCulturalPolitical;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyEconomics()
    {
        return $this->fieldOfStudyEconomics;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $fieldOfStudyEconomics ARGDESCRIPTION
     */
    public function setFieldOfStudyEconomics($fieldOfStudyEconomics)
    {
        $this->fieldOfStudyEconomics = $fieldOfStudyEconomics;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyOther()
    {
        return $this->fieldOfStudyOther;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $fieldOfStudyOther ARGDESCRIPTION
     */
    public function setFieldOfStudyOther($fieldOfStudyOther)
    {
        $this->fieldOfStudyOther = $fieldOfStudyOther;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getDataSize()
    {
        return $this->dataSize;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $dataSize ARGDESCRIPTION
     */
    public function setDataSize($dataSize)
    {
        $this->dataSize = $dataSize;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getVariablesObserved()
    {
        return $this->variablesObserved;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $variablesObserved ARGDESCRIPTION
     */
    public function setVariablesObserved($variablesObserved)
    {
        $this->variablesObserved = $variablesObserved;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethodFieldSampling()
    {
        return $this->collectionMethodFieldSampling;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $collectionMethodFieldSampling ARGDESCRIPTION
     */
    public function setCollectionMethodFieldSampling($collectionMethodFieldSampling)
    {
        $this->collectionMethodFieldSampling = $collectionMethodFieldSampling;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethodSimulatedGenerated()
    {
        return $this->collectionMethodSimulatedGenerated;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $collectionMethodSimulatedGenerated ARGDESCRIPTION
     */
    public function setCollectionMethodSimulatedGenerated($collectionMethodSimulatedGenerated)
    {
        $this->collectionMethodSimulatedGenerated = $collectionMethodSimulatedGenerated;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethodLaboratory()
    {
        return $this->collectionMethodLaboratory;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $collectionMethodLaboratory ARGDESCRIPTION
     */
    public function setCollectionMethodLaboratory($collectionMethodLaboratory)
    {
        $this->collectionMethodLaboratory = $collectionMethodLaboratory;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethodLiteratureBased()
    {
        return $this->collectionMethodLiteratureBased;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $collectionMethodLiteratureBased ARGDESCRIPTION
     */
    public function setCollectionMethodLiteratureBased($collectionMethodLiteratureBased)
    {
        $this->collectionMethodLiteratureBased = $collectionMethodLiteratureBased;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethodRemoteSensing()
    {
        return $this->collectionMethodRemoteSensing;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $collectionMethodRemoteSensing ARGDESCRIPTION
     */
    public function setCollectionMethodRemoteSensing($collectionMethodRemoteSensing)
    {
        $this->collectionMethodRemoteSensing = $collectionMethodRemoteSensing;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethodOther()
    {
        return $this->collectionMethodOther;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $collectionMethodOther ARGDESCRIPTION
     */
    public function setCollectionMethodOther($collectionMethodOther)
    {
        $this->collectionMethodOther = $collectionMethodOther;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getEstimatedStartDate()
    {
        return $this->estimatedStartDate;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $estimatedStartDate ARGDESCRIPTION
     */
    public function setEstimatedStartDate($estimatedStartDate)
    {
        $this->estimatedStartDate = $estimatedStartDate;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getEstimatedEndDate()
    {
        return $this->estimatedEndDate;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $estimatedEndDate ARGDESCRIPTION
     */
    public function setEstimatedEndDate($estimatedEndDate)
    {
        $this->estimatedEndDate = $estimatedEndDate;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getSpatialDescription()
    {
        return $this->spatialDescription;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $spatialDescription ARGDESCRIPTION
     */
    public function setSpatialDescription($spatialDescription)
    {
        $this->spatialDescription = $spatialDescription;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getSpatialGeometry()
    {
        return $this->spatialGeometry;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $spatialGeometry ARGDESCRIPTION
     */
    public function setSpatialGeometry($spatialGeometry)
    {
        $this->spatialGeometry = $spatialGeometry;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethod()
    {
        return $this->collectionMethod;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $collectionMethod ARGDESCRIPTION
     */
    public function setCollectionMethod($collectionMethod)
    {
        $this->collectionMethod = $collectionMethod;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getNationalDataArchiveNODC()
    {
        return $this->nationalDataArchiveNODC;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $nationalDataArchiveNODC ARGDESCRIPTION
     */
    public function setNationalDataArchiveNODC($nationalDataArchiveNODC)
    {
        $this->nationalDataArchiveNODC = $nationalDataArchiveNODC;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getNationalDataArchiveUSEPAStoret()
    {
        return $this->nationalDataArchiveUSEPAStoret;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $nationalDataArchiveUSEPAStoret ARGDESCRIPTION
     */
    public function setNationalDataArchiveUSEPAStoret($nationalDataArchiveUSEPAStoret)
    {
        $this->nationalDataArchiveUSEPAStoret = $nationalDataArchiveUSEPAStoret;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getNationalDataArchiveGBIF()
    {
        return $this->nationalDataArchiveGBIF;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $nationalDataArchiveGBIF ARGDESCRIPTION
     */
    public function setNationalDataArchiveGBIF($nationalDataArchiveGBIF)
    {
        $this->nationalDataArchiveGBIF = $nationalDataArchiveGBIF;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getNationalDataArchiveNCBI()
    {
        return $this->nationalDataArchiveNCBI;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $nationalDataArchiveNCBI ARGDESCRIPTION
     */
    public function setNationalDataArchiveNCBI($nationalDataArchiveNCBI)
    {
        $this->nationalDataArchiveNCBI = $nationalDataArchiveNCBI;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getNationalDataArchiveDataGov()
    {
        return $this->nationalDataArchiveDataGov;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $nationalDataArchiveDataGov ARGDESCRIPTION
     */
    public function setNationalDataArchiveDataGov($nationalDataArchiveDataGov)
    {
        $this->nationalDataArchiveDataGov = $nationalDataArchiveDataGov;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getNationalDataArchiveOther()
    {
        return $this->nationalDataArchiveOther;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $nationalDataArchiveOther ARGDESCRIPTION
     */
    public function setNationalDataArchiveOther($nationalDataArchiveOther)
    {
        $this->nationalDataArchiveOther = $nationalDataArchiveOther;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getEthicalIssues()
    {
        return $this->ethicalIssues;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $ethicalIssues ARGDESCRIPTION
     */
    public function setEthicalIssues($ethicalIssues)
    {
        $this->ethicalIssues = $ethicalIssues;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getEthicalIssuesExplanation()
    {
        return $this->ethicalIssuesExplanation;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $ethicalIssuesExplanation ARGDESCRIPTION
     */
    public function setEthicalIssuesExplanation($ethicalIssuesExplanation)
    {
        $this->ethicalIssuesExplanation = $ethicalIssuesExplanation;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getRemarks()
    {
        return $this->remarks;
    }

    /**
     * METHODDESCRIPTION
     * 
     * @access public
     * @param ARGTYPE $remarks ARGDESCRIPTION
     */
    public function setRemarks($remarks)
    {
        $this->remarks = $remarks;
    }
}

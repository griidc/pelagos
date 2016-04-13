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
     * @var PROPTYPE
     */
    protected $status;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $researchGroup;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $title;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $primaryPointOfContact;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $secondaryPointOfContact;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $abstract;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $fieldOfStudyEcologicalBiological;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $fieldOfStudyPhysicalOceanography;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $fieldOfStudyAtmospheric;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $fieldOfStudyChemical;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $fieldOfStudyHumanHealth;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $fieldOfStudySocialCulturalPolitical;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $fieldOfStudyEconomics;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $fieldOfStudyOther;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $dataSize;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $variablesObserved;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $collectionMethodFieldSampling;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $collectionMethodSimulatedGenerated;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $collectionMethodLaboratory;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $collectionMethodLiteratureBased;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $collectionMethodRemoteSensing;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $collectionMethodOther;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $estimatedStartDate;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $estimatedEndDate;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $spatialDescription;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $spatialGeometry;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $collectionMethod;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $nationalDataArchiveNODC;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $nationalDataArchiveUSEPAStoret;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $nationalDataArchiveGBIF;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $nationalDataArchiveNCBI;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $nationalDataArchiveDataGov;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $nationalDataArchiveOther;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $ethicalIssues;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $ethicalIssuesExplanation;

    /**
     * PROPDESCRIPTION
     *
     * @var PROPTYPE
     */
    protected $remarks;

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $status ARGDESCRIPTION
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getResearchGroup()
    {
        return $this->researchGroup;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $researchGroup ARGDESCRIPTION
     *
     * @return void
     */
    public function setResearchGroup($researchGroup)
    {
        $this->researchGroup = $researchGroup;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $title ARGDESCRIPTION
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getPrimaryPointOfContact()
    {
        return $this->primaryPointOfContact;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $primaryPointOfContact ARGDESCRIPTION
     *
     * @return void
     */
    public function setPrimaryPointOfContact($primaryPointOfContact)
    {
        $this->primaryPointOfContact = $primaryPointOfContact;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getSecondaryPointOfContact()
    {
        return $this->secondaryPointOfContact;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $secondaryPointOfContact ARGDESCRIPTION
     *
     * @return void
     */
    public function setSecondaryPointOfContact($secondaryPointOfContact)
    {
        $this->secondaryPointOfContact = $secondaryPointOfContact;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $abstract ARGDESCRIPTION
     *
     * @return void
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyEcologicalBiological()
    {
        return $this->fieldOfStudyEcologicalBiological;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $fieldOfStudyEcologicalBiological ARGDESCRIPTION
     *
     * @return void
     */
    public function setFieldOfStudyEcologicalBiological($fieldOfStudyEcologicalBiological)
    {
        $this->fieldOfStudyEcologicalBiological = $fieldOfStudyEcologicalBiological;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyPhysicalOceanography()
    {
        return $this->fieldOfStudyPhysicalOceanography;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $fieldOfStudyPhysicalOceanography ARGDESCRIPTION
     *
     * @return void
     */
    public function setFieldOfStudyPhysicalOceanography($fieldOfStudyPhysicalOceanography)
    {
        $this->fieldOfStudyPhysicalOceanography = $fieldOfStudyPhysicalOceanography;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyAtmospheric()
    {
        return $this->fieldOfStudyAtmospheric;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $fieldOfStudyAtmospheric ARGDESCRIPTION
     *
     * @return void
     */
    public function setFieldOfStudyAtmospheric($fieldOfStudyAtmospheric)
    {
        $this->fieldOfStudyAtmospheric = $fieldOfStudyAtmospheric;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyChemical()
    {
        return $this->fieldOfStudyChemical;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $fieldOfStudyChemical ARGDESCRIPTION
     *
     * @return void
     */
    public function setFieldOfStudyChemical($fieldOfStudyChemical)
    {
        $this->fieldOfStudyChemical = $fieldOfStudyChemical;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyHumanHealth()
    {
        return $this->fieldOfStudyHumanHealth;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $fieldOfStudyHumanHealth ARGDESCRIPTION
     *
     * @return void
     */
    public function setFieldOfStudyHumanHealth($fieldOfStudyHumanHealth)
    {
        $this->fieldOfStudyHumanHealth = $fieldOfStudyHumanHealth;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudySocialCulturalPolitical()
    {
        return $this->fieldOfStudySocialCulturalPolitical;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $fieldOfStudySocialCulturalPolitical ARGDESCRIPTION
     *
     * @return void
     */
    public function setFieldOfStudySocialCulturalPolitical($fieldOfStudySocialCulturalPolitical)
    {
        $this->fieldOfStudySocialCulturalPolitical = $fieldOfStudySocialCulturalPolitical;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyEconomics()
    {
        return $this->fieldOfStudyEconomics;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $fieldOfStudyEconomics ARGDESCRIPTION
     *
     * @return void
     */
    public function setFieldOfStudyEconomics($fieldOfStudyEconomics)
    {
        $this->fieldOfStudyEconomics = $fieldOfStudyEconomics;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getFieldOfStudyOther()
    {
        return $this->fieldOfStudyOther;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $fieldOfStudyOther ARGDESCRIPTION
     *
     * @return void
     */
    public function setFieldOfStudyOther($fieldOfStudyOther)
    {
        $this->fieldOfStudyOther = $fieldOfStudyOther;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getDataSize()
    {
        return $this->dataSize;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $dataSize ARGDESCRIPTION
     *
     * @return void
     */
    public function setDataSize($dataSize)
    {
        $this->dataSize = $dataSize;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getVariablesObserved()
    {
        return $this->variablesObserved;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $variablesObserved ARGDESCRIPTION
     *
     * @return void
     */
    public function setVariablesObserved($variablesObserved)
    {
        $this->variablesObserved = $variablesObserved;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethodFieldSampling()
    {
        return $this->collectionMethodFieldSampling;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $collectionMethodFieldSampling ARGDESCRIPTION
     *
     * @return void
     */
    public function setCollectionMethodFieldSampling($collectionMethodFieldSampling)
    {
        $this->collectionMethodFieldSampling = $collectionMethodFieldSampling;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethodSimulatedGenerated()
    {
        return $this->collectionMethodSimulatedGenerated;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $collectionMethodSimulatedGenerated ARGDESCRIPTION
     *
     * @return void
     */
    public function setCollectionMethodSimulatedGenerated($collectionMethodSimulatedGenerated)
    {
        $this->collectionMethodSimulatedGenerated = $collectionMethodSimulatedGenerated;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethodLaboratory()
    {
        return $this->collectionMethodLaboratory;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $collectionMethodLaboratory ARGDESCRIPTION
     *
     * @return void
     */
    public function setCollectionMethodLaboratory($collectionMethodLaboratory)
    {
        $this->collectionMethodLaboratory = $collectionMethodLaboratory;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethodLiteratureBased()
    {
        return $this->collectionMethodLiteratureBased;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $collectionMethodLiteratureBased ARGDESCRIPTION
     *
     * @return void
     */
    public function setCollectionMethodLiteratureBased($collectionMethodLiteratureBased)
    {
        $this->collectionMethodLiteratureBased = $collectionMethodLiteratureBased;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethodRemoteSensing()
    {
        return $this->collectionMethodRemoteSensing;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $collectionMethodRemoteSensing ARGDESCRIPTION
     *
     * @return void
     */
    public function setCollectionMethodRemoteSensing($collectionMethodRemoteSensing)
    {
        $this->collectionMethodRemoteSensing = $collectionMethodRemoteSensing;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethodOther()
    {
        return $this->collectionMethodOther;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $collectionMethodOther ARGDESCRIPTION
     *
     * @return void
     */
    public function setCollectionMethodOther($collectionMethodOther)
    {
        $this->collectionMethodOther = $collectionMethodOther;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getEstimatedStartDate()
    {
        return $this->estimatedStartDate;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $estimatedStartDate ARGDESCRIPTION
     *
     * @return void
     */
    public function setEstimatedStartDate($estimatedStartDate)
    {
        $this->estimatedStartDate = $estimatedStartDate;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getEstimatedEndDate()
    {
        return $this->estimatedEndDate;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $estimatedEndDate ARGDESCRIPTION
     *
     * @return void
     */
    public function setEstimatedEndDate($estimatedEndDate)
    {
        $this->estimatedEndDate = $estimatedEndDate;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getSpatialDescription()
    {
        return $this->spatialDescription;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $spatialDescription ARGDESCRIPTION
     *
     * @return void
     */
    public function setSpatialDescription($spatialDescription)
    {
        $this->spatialDescription = $spatialDescription;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getSpatialGeometry()
    {
        return $this->spatialGeometry;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $spatialGeometry ARGDESCRIPTION
     *
     * @return void
     */
    public function setSpatialGeometry($spatialGeometry)
    {
        $this->spatialGeometry = $spatialGeometry;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getCollectionMethod()
    {
        return $this->collectionMethod;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $collectionMethod ARGDESCRIPTION
     *
     * @return void
     */
    public function setCollectionMethod($collectionMethod)
    {
        $this->collectionMethod = $collectionMethod;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getNationalDataArchiveNODC()
    {
        return $this->nationalDataArchiveNODC;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $nationalDataArchiveNODC ARGDESCRIPTION
     *
     * @return void
     */
    public function setNationalDataArchiveNODC($nationalDataArchiveNODC)
    {
        $this->nationalDataArchiveNODC = $nationalDataArchiveNODC;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getNationalDataArchiveUSEPAStoret()
    {
        return $this->nationalDataArchiveUSEPAStoret;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $nationalDataArchiveUSEPAStoret ARGDESCRIPTION
     *
     * @return void
     */
    public function setNationalDataArchiveUSEPAStoret($nationalDataArchiveUSEPAStoret)
    {
        $this->nationalDataArchiveUSEPAStoret = $nationalDataArchiveUSEPAStoret;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getNationalDataArchiveGBIF()
    {
        return $this->nationalDataArchiveGBIF;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $nationalDataArchiveGBIF ARGDESCRIPTION
     *
     * @return void
     */
    public function setNationalDataArchiveGBIF($nationalDataArchiveGBIF)
    {
        $this->nationalDataArchiveGBIF = $nationalDataArchiveGBIF;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getNationalDataArchiveNCBI()
    {
        return $this->nationalDataArchiveNCBI;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $nationalDataArchiveNCBI ARGDESCRIPTION
     *
     * @return void
     */
    public function setNationalDataArchiveNCBI($nationalDataArchiveNCBI)
    {
        $this->nationalDataArchiveNCBI = $nationalDataArchiveNCBI;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getNationalDataArchiveDataGov()
    {
        return $this->nationalDataArchiveDataGov;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $nationalDataArchiveDataGov ARGDESCRIPTION
     *
     * @return void
     */
    public function setNationalDataArchiveDataGov($nationalDataArchiveDataGov)
    {
        $this->nationalDataArchiveDataGov = $nationalDataArchiveDataGov;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getNationalDataArchiveOther()
    {
        return $this->nationalDataArchiveOther;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $nationalDataArchiveOther ARGDESCRIPTION
     *
     * @return void
     */
    public function setNationalDataArchiveOther($nationalDataArchiveOther)
    {
        $this->nationalDataArchiveOther = $nationalDataArchiveOther;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getEthicalIssues()
    {
        return $this->ethicalIssues;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $ethicalIssues ARGDESCRIPTION
     *
     * @return void
     */
    public function setEthicalIssues($ethicalIssues)
    {
        $this->ethicalIssues = $ethicalIssues;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getEthicalIssuesExplanation()
    {
        return $this->ethicalIssuesExplanation;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $ethicalIssuesExplanation ARGDESCRIPTION
     *
     * @return void
     */
    public function setEthicalIssuesExplanation($ethicalIssuesExplanation)
    {
        $this->ethicalIssuesExplanation = $ethicalIssuesExplanation;
    }

    /**
     * METHODDESCRIPTION
     *
     * @return RETURNTYPE RETURNDESCRIPTION
     */
    public function getRemarks()
    {
        return $this->remarks;
    }

    /**
     * METHODDESCRIPTION
     *
     * @param ARGTYPE $remarks ARGDESCRIPTION
     *
     * @return void
     */
    public function setRemarks($remarks)
    {
        $this->remarks = $remarks;
    }
}

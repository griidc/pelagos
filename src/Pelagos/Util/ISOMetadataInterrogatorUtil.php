<?php

namespace Pelagos\Util;

use Pelagos\Entity\DatasetSubmission;

/**
 * A utility class for extracting information from ISO metadata.
 */
class ISOMetadataInterrogatorUtil
{
    /**
     * Metadata file as SimpleXML object.
     *
     * @var \SimpleXMLElement
     */
    protected $xmlMetadata;

    /**
     * The dataset submission object.
     *
     * @var DatasetSubmission
     */
    protected $datasetSubmission;

    /**
     * Constructor for this class.
     *
     * @param \SimpleXmlElement $xmlMetadata       The XML object to examine for values.
     * @param DatasetSubmission $datasetSubmission The Dataset Submission to examine for values, populate & return.
     */
    public function __construct(\SimpleXmlElement $xmlMetadata, DatasetSubmission $datasetSubmission)
    {
        $this->xmlMetadata = $xmlMetadata;
        $this->datasetSubmission = $datasetSubmission;
    }

    /**
     * Returns a populated Dataset Submission object.
     *
     * Return a DatasetSubmission object populated with values from both
     * the Dataset Submission and the XML object, with values from the
     * XML object overriding any values from the Dataset Submission.
     *
     * @return DatasetSubmission
     */
    public function returnDsWithXmlPri()
    {
        return $this->datasetSubmission;
    }

    /**
     * Returns a populated Dataset Submission object.
     *
     * Return a DatasetSubmission object populated with values from both
     * the Dataset Submission and the XML object, with values from the passed
     * Dataset Submission overriding any values from the XML object.
     *
     * @return DatasetSubmission
     */
    public function returnDsWithDatasetSubmissionPri()
    {
        return $this->datasetSubmission;
    }

    /**
     * Extracts title from XML metadata.
     *
     * @return string|null Returns the title as a string, or null.
     */
    private function extractTitle()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:citation' .
                 '/gmd:CI_Citation' .
                 '/gmd:title' .
                 '/gco:CharacterString' .
                 '/text()';

        return $this->querySingle($query);
    }

    /**
     * Extracts short title from XML metadata.
     *
     * @return string|null Returns the title as a string, or null.
     */
    private function extractShortTitle()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:citation' .
                 '/gmd:CI_Citation' .
                 '/gmd:alternateTitle' .
                 '/gco:CharacterString' .
                 '/text()';

        return $this->querySingle($query);
    }

    /**
     * Extracts abstract from XML metadata.
     *
     * @return string|null Returns the abstract as a string, or null.
     */
    private function extractAbstract()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:abstract' .
                 '/gco:CharacterString' .
                 '/text()';

        return $this->querySingle($query);
    }

    /**
     * Extracts purpose from XML metadata.
     *
     * @return string|null Returns the purpose as a string, or null.
     */
    private function extractPurpose()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:purpose' .
                 '/gco:CharacterString' .
                 '/text()';

        return $this->querySingle($query);
    }

    /**
     * Extracts supplemental parameters from XML metadata.
     *
     * @return string|null Returns the parameters as a string, or null.
     */
    private function extractSuppParams()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:supplementalInformation' .
                 '/gco:CharacterString' .
                 '/text()';

        $supplementalData = $this->querySingle($query);
        if (null !== $supplementalData) {
            $parameters = $supplementaldata[0];
            if (null !== $parameters) {
                return $parameters;
            } else {
                return null;
            }
        }
    }

    /**
     * Extracts supplemental methods from XML metadata.
     *
     * @return string|null Returns the methods as a string, or null.
     */
    private function extractSuppMethods()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:supplementalInformation' .
                 '/gco:CharacterString' .
                 '/text()';

        $supplementalData = $this->querySingle($query);
        if (null !== $supplementalData) {
            $methods = $supplementaldata[1];
            if (null !== $methods) {
                return $methods;
            } else {
                return null;
            }
        }
    }

    /**
     * Extracts supplemental instruments from XML metadata.
     *
     * @return string|null Returns the instruments as a string, or null.
     */
    private function extractSuppInstruments()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:supplementalInformation' .
                 '/gco:CharacterString' .
                 '/text()';

        $supplementalData = $this->querySingle($query);
        if (null !== $supplementalData) {
            $instruments = $supplementaldata[2];
            if (null !== $instruments) {
                return $instruments;
            } else {
                return null;
            }
        }
    }

    /**
     * Extracts sample scales and rates from XML metadata.
     *
     * @return string|null Returns the sample scales and rates as a string, or null.
     */
    private function extractSuppSampScalesRates()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:supplementalInformation' .
                 '/gco:CharacterString' .
                 '/text()';

        $supplementalData = $this->querySingle($query);
        if (null !== $supplementalData) {
            $sampScalesAndRates = $supplementaldata[3];
            if (null !== $supplementalData) {
                return $supplementalData;
            } else {
                return null;
            }
        }
    }

    /**
     * Extracts error analysis from XML metadata.
     *
     * @return string|null Returns the error analysis as a string, or null.
     */
    private function extractSuppErrorAnalysis()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:supplementalInformation' .
                 '/gco:CharacterString' .
                 '/text()';

        $supplementalData = $this->querySingle($query);
        if (null !== $supplementalData) {
            $errorAnalysis = $supplementaldata[4];
            if (null !== $errorAnalysis) {
                return $errorAnalysis;
            } else {
                return null;
            }
        }
    }

    /**
     * Extracts provenance from XML metadata.
     *
     * @return string|null Returns the provenance as a string, or null.
     */
    private function extractSuppProvenance()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:supplementalInformation' .
                 '/gco:CharacterString' .
                 '/text()';

        $supplementalData = $this->querySingle($query);
        if (null !== $supplementalData) {
            $provenance = $supplementaldata[5];
            if (null !== $provenance) {
                return $provenance;
            } else {
                return null;
            }
        }
    }

    /**
     * Extracts reference date from XML metadata.
     *
     * @return \DateTime|null Returns the reference date as a DateTime, or null.
     */
    private function extractReferenceDate()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:citation' .
                 '/gmd:CI_Citation' .
                 '/gmd:date' .
                 '/gmd:CI_Date' .
                 '/gmd:date' .
                 '/gco:Date';

        $date = $this->querySingle($query);
        if (null !== $date and preg_match('/\d\d\d\d-\d{1,2}-\d{1,2}/', $date)) {
            $dateTime = new \DateTime($date, new \DateTimeZone('UTC'));
            return $dateTime;
        } else {
            return null;
        }
    }

    /**
     * Extracts  from XML metadata.
     *
     * @return string|null Returns the  as a string, or null.
     */
    private function extractReferenceType()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:citation' .
                 '/gmd:CI_Citation' .
                 '/gmd:date' .
                 '/gmd:CI_Date' .
                 '/gmd:dateType' .
                 '/gmd:CI_DateTypeCode';

        return $this->querySingle($query);
    }

    /**
     * Extracts theme keywords from XML metadata.
     *
     * @return array Returns the theme keywords as an array, or empty array.
     */
    private function extractThemeKeywords()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:descriptiveKeywords' .
                 '/gmd:MD_Keywords' .
                 '/gmd:type[descendant::text()="theme"]' .
                 '/parent::gmd:MD_Keywords' .
                 '/gmd:keyword' .
                 '/gco:CharacterString' .
                 '/text()';

        return $this->queryMultiple($query);
    }

    /**
     * Extracts place keywords from XML metadata.
     *
     * @return array Returns the place keywords as an array, or empty array.
     */
    private function extractPlaceKeywords()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:descriptiveKeywords' .
                 '/gmd:MD_Keywords' .
                 '/gmd:type[descendant::text()="place"]' .
                 '/parent::gmd:MD_Keywords' .
                 '/gmd:keyword' .
                 '/gco:CharacterString' .
                 '/text()';

        return $this->queryMultiple($query);
    }

    /**
     * Extracts topic keywords from XML metadata.
     *
     * @return array Returns the topic keywords as an array, or empty array.
     */
    private function extractTopicKeywords()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:topicCategory' .
                 '/gmd:MD_TopicCategoryCode' .
                 '/text()';

        return $this->queryMultiple($query);
    }

    /**
     * Extracts GML from XML metadata.
     *
     * @return string|null Returns the GML as a string, or null.
     */
    private function extractSpacialExtent()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:extent' .
                 '/gmd:EX_Extent' .
                 '/gmd:geographicElement' .
                 '/gmd:EX_BoundingPolygon' .
                 '/gmd:polygon' .
                 '/child::*';

        return $this->querySingleGml($query);
    }

    /**
     * Extracts  from XML metadata.
     *
     * @return string|null Returns the  as a string, or null.
     */
    private function extractTemporalExtentDesc()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:extent' .
                 '/gmd:EX_Extent' .
                 '/gmd:description' .
                 '/text()';

        return $this->querySingle($query);
    }

    /**
     * Extracts temporal begin position from XML metadata.
     *
     * @return \DateTime|null Returns the starting date as a DateTime, or null.
     */
    private function extractTemporalExtentBeginPosition()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:extent' .
                 '/gmd:EX_Extent' .
                 '/gmd:temporalElement' .
                 '/gmd:EX_TemporalExtent' .
                 '/gmd:extent' .
                 '/gml:TimePeriod' .
                 '/gml:beginPosition' .
                 '/text()';

        $date = $this->querySingle($query);
        if (null !== $date and preg_match('/\d\d\d\d-\d{1,2}-\d{1,2}/', $date)) {
            $dateTime = new \DateTime($date, new \DateTimeZone('UTC'));
            return $dateTime;
        } else {
            return null;
        }
    }

    /**
     * Extracts temporal end position from XML metadata.
     *
     * @return \DateTime|null Returns the ending date as a DateTime, or null.
     */
    private function extractTemporalExtentEndPosition()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:extent' .
                 '/gmd:EX_Extent' .
                 '/gmd:temporalElement' .
                 '/gmd:EX_TemporalExtent' .
                 '/gmd:extent' .
                 '/gml:TimePeriod' .
                 '/gml:endPosition' .
                 '/text()';

        $date = $this->querySingle($query);
        if (null !== $date and preg_match('/\d\d\d\d-\d{1,2}-\d{1,2}/', $date)) {
            $dateTime = new \DateTime($date, new \DateTimeZone('UTC'));
            return $dateTime;
        } else {
            return null;
        }
    }

    /**
     * Extracts file format from XML metadata.
     *
     * @return string|null Returns the file format as a string, or null.
     */
    private function extractDistributionFormatName()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:distributionInfo' .
                 '/gmd:MD_Distribution' .
                 '/gmd:distributor' .
                 '/gmd:MD_Distributor' .
                 '/gmd:distributorFormat' .
                 '/gmd:MD_Format' .
                 '/gmd:name' .
                 '/gco:CharacterString' .
                 '/text()';

        return $this->querySingle($query);
    }

    /**
     * Extracts archive format from XML metadata.
     *
     * @return string|null Returns the archive format as a string, or null.
     */
    private function extractFileDecompressionTechnique()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:distributionInfo' .
                 '/gmd:MD_Distribution' .
                 '/gmd:distributor' .
                 '/gmd:MD_Distributor' .
                 '/gmd:distributorFormat' .
                 '/gmd:MD_Format' .
                 '/gmd:fileDecompressionTechnique' .
                 '/gco:CharacterString' .
                 '/text()';

        return $this->querySingle($query);
    }

    /**
     * Extracts URI of the dataset to which the metadata applies.
     *
     * @return string|null Returns the datasetURI as a string, or null.
     *
     * The NOAA ISO 19115 Geographic Information - Metadata Workbook
     * discusses this on page 22.  (MI_Metadata.pdf)
     */
    private function extractDatasetUri()
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:dataSetURI' .
                 '/gco:CharacterString' .
                 '/text()';

        return $this->querySingle($query);
    }

    /**
     * Runs xpath and returns resulting single value or null.
     *
     * @param string $xpath The xpath query to run.
     *
     * @return string|null Item queried in xpath.
     */
    private function querySingle($xpath)
    {
        $query = $this->xmlMetadata->xpath($xpath);

        if (false === $query) {
            // This is a best effort, so null if xpath fails.
            return null;
        }

        if (count($query) > 0) {
            // return first value as string.
            return (string) $query[0];
        } else {
            return null;
        }
    }

    /**
     * Runs xpath and returns resulting single value as GML or null.
     *
     * @param string $xpath The xpath query to run.
     *
     * @return string|null Item queried in xpath.
     */
    private function querySingleGml($xpath)
    {
        $query = $this->xmlMetadata->xpath($xpath);

        if (false === $query) {
            return null;
        }

        if (count($query) > 0) {
            $gml = $query[0]->asXML();
            return $gml;
        } else {
            return null;
        }
    }

    /**
     * Runs xpath and returns an array, or empty array.
     *
     * @param string $xpath The xpath query to run.
     *
     * @return array Result of items queried in xpath.
     */
    private function queryMultiple($xpath)
    {
        $query = $this->xmlMetadata->xpath($xpath);

        if (false === $query) {
            // This is a best effort, so empty array if xpath fails.
            return array();
        }

        $arrayOfStrings = array();
        // Cast results to string.
        foreach ($query as $key => $value) {
            $arrayOfStrings[$key] = (string) $value;
        }
        return $arrayOfStrings;
    }
}

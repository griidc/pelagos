<?php

namespace Pelagos\Util;

use Pelagos\Entity\DatasetSubmission;

/**
 * A utility class for extracting information from ISO metadata.
 */
class ISOMetadataInterrogatorUtil
{
    /**
     * Conditionally populates Dataset Submission object, by reference.
     *
     * Return a DatasetSubmission object populated with values from both
     * the Dataset Submission and the XML object, with values from the
     * XML object overriding any values from the Dataset Submission.
     *
     * @param \SimpleXmlElement $xmlMetadata       The XML to be read from.
     * @param DatasetSubmission $datasetSubmission The datasetSubmission object to be modified.
     *
     * @return void
     */
    public static function populateDatasetSubmissionWithXMLValues(\SimpleXmlElement $xmlMetadata, DatasetSubmission $datasetSubmission)
    {
        self::setIfHas($datasetSubmission, 'setTitle', self::extractTitle($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setShortTitle', self::extractShortTitle($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setAbstract', self::extractAbstract($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setPurpose', self::extractPurpose($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setSuppParams', self::extractSuppParams($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setSuppInstruments', self::extractSuppInstruments($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setSuppMethods', self::extractSuppMethods($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setSuppSampScalesRates', self::extractSuppSampScalesRates($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setSuppErrorAnalysis', self::extractSuppErrorAnalysis($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setSuppProvenance', self::extractSuppProvenance($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setReferenceDate', self::extractReferenceDate($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setReferenceDateType', self::extractReferenceType($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setThemeKeywords', self::extractThemeKeywords($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setPlaceKeywords', self::extractPlaceKeywords($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setTopicKeywords', self::extractTopicKeywords($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setSpatialExtent', self::extractSpacialExtent($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setTemporalExtentDesc', self::extractTemporalExtentDesc($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setTemporalExtentBeginPosition', self::extractTemporalExtentBeginPosition($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setTemporalExtentEndPosition', self::extractTemporalExtentEndPosition($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setDistributionFormatName', self::extractDistributionFormatName($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setFileDecompressionTechnique', self::extractFileDecompressionTechnique($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setDatasetFileUri', self::extractDatasetUri($xmlMetadata));
    }

    /**
     * Sets value in DatasetSubmission (by reference) if not null in XML.
     *
     * @param DatasetSubmission $ds     A DatasetSubmission object.
     * @param string            $setter The setter of DatasetSubmission for this attribute.
     * @param mixed             $value  The value of the attribute in the XML.
     *
     * @return void
     */
    protected static function setIfHas(DatasetSubmission $ds, $setter, $value)
    {
        if (null !== $value) {
            $ds->$setter($value);
        }
    }

    /**
     * Extracts title from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return string|null Returns the title as a string, or null.
     */
    protected static function extractTitle(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:citation' .
                 '/gmd:CI_Citation' .
                 '/gmd:title' .
                 '/gco:CharacterString' .
                 '/text()';

        return self::querySingle($xml, $query);
    }

    /**
     * Extracts short title from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return string|null Returns the title as a string, or null.
     */
    protected static function extractShortTitle(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:citation' .
                 '/gmd:CI_Citation' .
                 '/gmd:alternateTitle' .
                 '/gco:CharacterString' .
                 '/text()';

        return self::querySingle($xml, $query);
    }

    /**
     * Extracts abstract from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return string|null Returns the abstract as a string, or null.
     */
    protected static function extractAbstract(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:abstract' .
                 '/gco:CharacterString' .
                 '/text()';

        return self::querySingle($xml, $query);
    }

    /**
     * Extracts purpose from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return string|null Returns the purpose as a string, or null.
     */
    protected static function extractPurpose(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:purpose' .
                 '/gco:CharacterString' .
                 '/text()';

        return self::querySingle($xml, $query);
    }

    /**
     * Extracts supplemental parameters from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return string|null Returns the parameters as a string, or null.
     */
    protected static function extractSuppParams(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:supplementalInformation' .
                 '/gco:CharacterString' .
                 '/text()';

        $supplementalData = self::querySingle($xml, $query);
        if (null !== $supplementalData) {
            $supplementalList = preg_split('/\|/', $supplementalData);
            $parameters = $supplementalList[0];
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
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return string|null Returns the methods as a string, or null.
     */
    protected static function extractSuppMethods(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:supplementalInformation' .
                 '/gco:CharacterString' .
                 '/text()';

        $supplementalData = self::querySingle($xml, $query);
        if (null !== $supplementalData) {
            $supplementalList = preg_split('/\|/', $supplementalData);
            $methods = $supplementalList[1];
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
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return string|null Returns the instruments as a string, or null.
     */
    protected static function extractSuppInstruments(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:supplementalInformation' .
                 '/gco:CharacterString' .
                 '/text()';

        $supplementalData = self::querySingle($xml, $query);
        if (null !== $supplementalData) {
            $supplementalList = preg_split('/\|/', $supplementalData);
            $instruments = $supplementalList[2];
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
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return string|null Returns the sample scales and rates as a string, or null.
     */
    protected static function extractSuppSampScalesRates(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:supplementalInformation' .
                 '/gco:CharacterString' .
                 '/text()';

        $supplementalData = self::querySingle($xml, $query);
        if (null !== $supplementalData) {
            $supplementalList = preg_split('/\|/', $supplementalData);
            $sampScalesAndRates = $supplementalList[3];
            if (null !== $sampScalesAndRates) {
                return $sampScalesAndRates;
            } else {
                return null;
            }
        }
    }

    /**
     * Extracts error analysis from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return string|null Returns the error analysis as a string, or null.
     */
    protected static function extractSuppErrorAnalysis(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:supplementalInformation' .
                 '/gco:CharacterString' .
                 '/text()';

        $supplementalData = self::querySingle($xml, $query);
        if (null !== $supplementalData) {
            $supplementalList = preg_split('/\|/', $supplementalData);
            $errorAnalysis = $supplementalList[4];
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
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return string|null Returns the provenance as a string, or null.
     */
    protected static function extractSuppProvenance(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:supplementalInformation' .
                 '/gco:CharacterString' .
                 '/text()';

        $supplementalData = self::querySingle($xml, $query);
        if (null !== $supplementalData) {
            $supplementalList = preg_split('/\|/', $supplementalData);
            $provenance = $supplementalList[5];
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
    protected static function extractReferenceDate(\SimpleXmlElement $xml)
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

        $date = self::querySingle($xml, $query);
        if (null !== $date and preg_match('/\d\d\d\d-\d{1,2}-\d{1,2}/', $date)) {
            $dateTime = new \DateTime($date, new \DateTimeZone('UTC'));
            return $dateTime;
        } else {
            return null;
        }
    }

    /**
     * Extracts referenceDateType from XML metadata.
     *
     * @return string|null Returns the reference date type as a string, or null.
     */
    protected static function extractReferenceType(\SimpleXmlElement $xml)
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

        return self::querySingle($xml, $query);
    }

    /**
     * Extracts theme keywords from XML metadata.
     *
     * @return array Returns the theme keywords as an array, or empty array.
     */
    protected static function extractThemeKeywords(\SimpleXmlElement $xml)
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

        return self::queryMultiple($xml, $query);
    }

    /**
     * Extracts place keywords from XML metadata.
     *
     * @return array Returns the place keywords as an array, or empty array.
     */
    protected static function extractPlaceKeywords(\SimpleXmlElement $xml)
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

        return self::queryMultiple($xml, $query);
    }

    /**
     * Extracts topic keywords from XML metadata.
     *
     * @return array Returns the topic keywords as an array, or empty array.
     */
    protected static function extractTopicKeywords(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:topicCategory' .
                 '/gmd:MD_TopicCategoryCode' .
                 '/text()';

        return self::queryMultiple($xml, $query);
    }

    /**
     * Extracts GML from XML metadata.
     *
     * @return string|null Returns the GML as a string, or null.
     */
    protected static function extractSpacialExtent(\SimpleXmlElement $xml)
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

        return self::querySingleGml($xml, $query);
    }

    /**
     * Extracts temporal extent description from XML metadata.
     *
     * @return string|null Returns the temporal extent description as a string, or null.
     */
    protected static function extractTemporalExtentDesc(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:extent' .
                 '/gmd:EX_Extent' .
                 '/gmd:temporalElement' .
                 '/gmd:EX_TemporalExtent' .
                 '/gmd:extent/' .
                 '/gml:TimePeriod' .
                 '/gml:description' .
                 '/text()';

        return self::querySingle($xml, $query);
    }

    /**
     * Extracts temporal begin position from XML metadata.
     *
     * @return \DateTime|null Returns the starting date as a DateTime, or null.
     */
    protected static function extractTemporalExtentBeginPosition(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:extent' .
                 '/gmd:EX_Extent' .
                 '/gmd:temporalElement' .
                 '/gmd:EX_TemporalExtent' .
                 '/gmd:extent/' .
                 '/gml:TimePeriod' .
                 '/gml:beginPosition' .
                 '/text()';

        $date = self::querySingle($xml, $query);
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
    protected static function extractTemporalExtentEndPosition(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:extent' .
                 '/gmd:EX_Extent' .
                 '/gmd:temporalElement' .
                 '/gmd:EX_TemporalExtent' .
                 '/gmd:extent/' .
                 '/gml:TimePeriod' .
                 '/gml:endPosition' .
                 '/text()';

        $date = self::querySingle($xml, $query);
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
    protected static function extractDistributionFormatName(\SimpleXmlElement $xml)
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

        return self::querySingle($xml, $query);
    }

    /**
     * Extracts archive format from XML metadata.
     *
     * @return string|null Returns the archive format as a string, or null.
     */
    protected static function extractFileDecompressionTechnique(\SimpleXmlElement $xml)
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

        return self::querySingle($xml, $query);
    }

    /**
     * Extracts URI of the dataset to which the metadata applies.
     *
     * @return string|null Returns the datasetURI as a string, or null.
     *
     * The NOAA ISO 19115 Geographic Information - Metadata Workbook
     * discusses this on page 22.  (MI_Metadata.pdf)
     */
    protected static function extractDatasetUri(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:distributionInfo' .
                 '/gmd:MD_Distribution' .
                 '/gmd:distributor' .
                 '/gmd:MD_Distributor' .
                 '/gmd:distributorTransferOptions' .
                 '/gmd:MD_DigitalTransferOptions' .
                 '/gmd:onLine' .
                 '/gmd:CI_OnlineResource' .
                 '/gmd:linkage' .
                 '/gmd:URL' .
                 '/text()';

        return self::querySingle($xml, $query);
    }

    /**
     * Runs xpath and returns resulting single value or null.
     *
     * @param \SimpleXmlElement $xml   The XML to query.
     * @param string            $xpath The xpath query to run.
     *
     * @return string|null Item queried in xpath.
     */
    protected static function querySingle(\SimpleXmlElement $xml, $xpath)
    {
        $query = $xml->xpath($xpath);

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
     * @param \SimpleXmlElement $xml   The XML to query.
     * @param string            $xpath The xpath query to run.
     *
     * @return string|null Item queried in xpath.
     */
    protected static function querySingleGml(\SimpleXmlElement $xml, $xpath)
    {
        $query = $xml->xpath($xpath);

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
     * @param \SimpleXmlElement $xml   The XML to query.
     * @param string            $xpath The xpath query to run.
     *
     * @return array Result of items queried in xpath.
     */
    protected static function queryMultiple(\SimpleXmlElement $xml, $xpath)
    {
        $query = $xml->xpath($xpath);

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

<?php

namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;

use Pelagos\Entity\DataCenter;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DistributionPoint;
use Pelagos\Entity\Person;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;
use Pelagos\Entity\PersonDatasetSubmissionMetadataContact;

/**
 * A utility class for extracting information from ISO metadata.
 */
class ISOMetadataExtractorUtil
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
     * @param EntityManager     $entityManager     An entity manager.
     *
     * @return void
     */
    public static function populateDatasetSubmissionWithXMLValues(\SimpleXmlElement $xmlMetadata, DatasetSubmission &$datasetSubmission, EntityManager $entityManager)
    {
        $pointsOfContact = self::extractPointsOfContact($xmlMetadata, $datasetSubmission, $entityManager);
        foreach ($pointsOfContact as $poc) {
            self::setIfHas($datasetSubmission, 'addDatasetContact', $poc);
        }

        // This always returns a single POC, not an array of POC.
        $metadataContact = self::extractMetadataContact($xmlMetadata, $datasetSubmission, $entityManager);
        self::setIfHas($datasetSubmission, 'addMetadataContact', $metadataContact);

        $distributionPoints = self::extractDistributionPoint($xmlMetadata, $datasetSubmission, $entityManager);
        foreach ($distributionPoints as $distributionPoint) {
            self::setIfHas($datasetSubmission, 'addDistributionPoint', $distributionPoint);
        }

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
        self::setIfHas($datasetSubmission, 'setSpatialExtent', self::extractSpatialExtent($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setSpatialExtentDescription', self::extractSpatialExtentDescription($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setTemporalExtentDesc', self::extractTemporalExtentDesc($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setTemporalExtentBeginPosition', self::extractTemporalExtentBeginPosition($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setTemporalExtentEndPosition', self::extractTemporalExtentEndPosition($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setDistributionFormatName', self::extractDistributionFormatName($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setFileDecompressionTechnique', self::extractFileDecompressionTechnique($xmlMetadata));
        self::setIfHas($datasetSubmission, 'setTemporalExtentNilReasonType', self::extractTemporalExtentNilReasonType($xmlMetadata));
    }

    /**
     * Sets value in DatasetSubmission (by reference) if not null in XML.
     *
     * @param DatasetSubmission $ds     A DatasetSubmission object.
     * @param string            $setter DatasetSubmission's setter/adder for the attribute.
     * @param mixed             $value  The value of the attribute derived from the XML.
     *
     * @return void
     */
    protected static function setIfHas(DatasetSubmission &$ds, $setter, $value)
    {
        if (!empty($value)) {
            try {
                $ds->$setter($value);
            } catch (\InvalidArgumentException $e) {
                // couldn't set.
            }
        }
    }

    /**
     * Get the 1st email addresses from the 1st POC from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     * @param DatasetSubmission $ds  A Pelagos DatasetSubmission instance.
     * @param EntityManager     $em  An entity manager.
     *
     * @return Array of PersonDatasetSubmissionDatasetContacts, or empty array if none.
     */
    public static function get1stEmailAddressesFrom1stPointOfContact(\SimpleXmlElement $xml, DatasetSubmission $ds, EntityManager $em)
    {
        $targetEmailAddress = null;

        $query = '/gmi:MI_Metadata' .
            '/gmd:identificationInfo' .
            '/gmd:MD_DataIdentification' .
            '/gmd:pointOfContact';


        $pointsOfContact = @$xml->xpath($query);

        if (!empty($pointsOfContact)) {
            $pointOfContact = $pointsOfContact[0];

                // for this POC get the 1st email addresses
                $query = './gmd:CI_ResponsibleParty' .
                    '/gmd:contactInfo' .
                    '/gmd:CI_Contact' .
                    '/gmd:address' .
                    '/gmd:CI_Address' .
                    '/gmd:electronicMailAddress' .
                    '/gco:CharacterString';

            $targetEmailAddress = self::querySingle($pointOfContact, $query);
        }
        return $targetEmailAddress;
    }

    /**
     * Get the all email addresses from all POCs from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     * @param DatasetSubmission $ds  A Pelagos DatasetSubmission instance.
     * @param EntityManager     $em  An entity manager.
     *
     * @return Array of PersonDatasetSubmissionDatasetContacts, or empty array if none.
     */
    public static function getAllEmailAddressesForAllPointsOfContact(\SimpleXmlElement $xml, DatasetSubmission $ds, EntityManager $em)
    {
        $emailAddressColection = array();

        $query = '/gmi:MI_Metadata' .
            '/gmd:identificationInfo' .
            '/gmd:MD_DataIdentification' .
            '/gmd:pointOfContact';


        $pointsOfContact = @$xml->xpath($query);

        if (!empty($pointsOfContact)) {
            foreach ($pointsOfContact as $pointOfContact) {

                // for each POC get all email addresses
                $query = './gmd:CI_ResponsibleParty' .
                    '/gmd:contactInfo' .
                    '/gmd:CI_Contact' .
                    '/gmd:address' .
                    '/gmd:CI_Address' .
                    '/gmd:electronicMailAddress' .
                    '/gco:CharacterString';

                $allEmailAddresses = self::queryMultiple($pointOfContact, $query);
                if (!empty($allEmailAddresses)) {
                    foreach ($allEmailAddresses as $emailAddress) {
                        $emailAddressColection[] = $emailAddress;
                    }
                }
            }
        }
        return $emailAddressColection;
    }

    /**
     * Determines the dataset contact from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     * @param DatasetSubmission $ds  A Pelagos DatasetSubmission instance.
     * @param EntityManager     $em  An entity manager.
     *
     * @return Array of PersonDatasetSubmissionDatasetContacts, or empty array if none.
     */
    public static function extractPointsOfContact(\SimpleXmlElement $xml, DatasetSubmission $ds, EntityManager $em)
    {
        $personDatasetSubmissionDatasetContacts = array();

        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:pointOfContact';

        $pointsOfContact = @$xml->xpath($query);

        if (!empty($pointsOfContact)) {
            foreach ($pointsOfContact as $pointOfContact) {

                // Find Person
                $query = './gmd:CI_ResponsibleParty' .
                         '/gmd:contactInfo' .
                         '/gmd:CI_Contact' .
                         '/gmd:address' .
                         '/gmd:CI_Address' .
                         '/gmd:electronicMailAddress' .
                         '/gco:CharacterString';

                $email = self::querySingle($pointOfContact, $query);
                $personArray = $em->getRepository(Person::class)->findBy(
                    array('emailAddress' => $email)
                );

                if (count($personArray) > 0) {
                    $person = $personArray[0];
                    // Find Role
                    $query = './gmd:CI_ResponsibleParty' .
                             '/gmd:role' .
                             '/gmd:CI_RoleCode';

                    $role = self::querySingle($pointOfContact, $query);
                } else {
                    $person = null;
                }

                // If we've found a person build personDatasetSubmissionDatasetContact.
                if ($person instanceof Person) {
                    $personDatasetSubmissionDatasetContact = new PersonDatasetSubmissionDatasetContact();
                    $personDatasetSubmissionDatasetContact->setPerson($person);
                    // Only set role if it is a valid role, otherwise leave unset.
                    if (null !== $role and array_key_exists($role, PersonDatasetSubmissionDatasetContact::ROLES)) {
                        $personDatasetSubmissionDatasetContact->setRole($role);
                    }
                    $personDatasetSubmissionDatasetContact->setDatasetSubmission($ds);
                    $personDatasetSubmissionDatasetContacts[] = $personDatasetSubmissionDatasetContact;
                }
            }
        }
        return $personDatasetSubmissionDatasetContacts;
    }

    /**
     * Determines the metadata contact from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     * @param DatasetSubmission $ds  A Pelagos DatasetSubmission instance.
     * @param EntityManager     $em  An entity manager.
     *
     * @return PersonDatasetSubmissionMetadataContact|null Returns the metadata contact, or null.
     */
    protected static function extractMetadataContact(\SimpleXmlElement $xml, DatasetSubmission $ds, EntityManager $em)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:contact[1]' .
                 '/gmd:CI_ResponsibleParty' .
                 '/gmd:contactInfo' .
                 '/gmd:CI_Contact' .
                 '/gmd:address' .
                 '/gmd:CI_Address' .
                 '/gmd:electronicMailAddress' .
                 '/gco:CharacterString';

        $email = self::querySingle($xml, $query);

        $people = $em->getRepository(Person::class)->findBy(
            array('emailAddress' => $email)
        );

        if (count($people) > 0) {
            $person = $people[0];
        } else {
            $person = null;
        }

        $query = '/gmi:MI_Metadata' .
                 '/gmd:contact[1]' .
                 '/gmd:CI_ResponsibleParty' .
                 '/gmd:role' .
                 '/gmd:CI_RoleCode';

        $role = self::querySingle($xml, $query);

        if ($person instanceof Person) {
            $personDatasetSubmissionMetadataContact = new PersonDatasetSubmissionMetadataContact();
            $personDatasetSubmissionMetadataContact->setPerson($person);
            // Only set role if it is a valid role, otherwise leave unset.
            if (null !== $role and array_key_exists($role, PersonDatasetSubmissionMetadataContact::ROLES)) {
                $personDatasetSubmissionMetadataContact->setRole($role);
            }
            $personDatasetSubmissionMetadataContact->setDatasetSubmission($ds);
            return $personDatasetSubmissionMetadataContact;
        } else {
            return null;
        }
    }

    /**
     * Determines the distribution point from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     * @param DatasetSubmission $ds  A Pelagos DatasetSubmission instance.
     * @param EntityManager     $em  An entity manager.
     *
     * @return Array of DistributionPoint, or empty array if none.
     */
    public static function extractDistributionPoint(\SimpleXmlElement $xml, DatasetSubmission $ds, EntityManager $em)
    {
        $distributionPoints = array();

        $query = '/gmi:MI_Metadata' .
                 '/gmd:distributionInfo' .
                 '/gmd:MD_Distribution' .
                 '/gmd:distributor';

        $distributors = @$xml->xpath($query);

        if (!empty($distributors)) {
            foreach ($distributors as $distributor) {
                // Find distributor by Email
                $query = './gmd:MD_Distributor' .
                         '/gmd:distributorContact' .
                         '/gmd:CI_ResponsibleParty' .
                         '/gmd:contactInfo' .
                         '/gmd:CI_Contact' .
                         '/gmd:address' .
                         '/gmd:CI_Address' .
                         '/gmd:electronicMailAddress' .
                         '/gco:CharacterString';

                $email = strtolower(self::querySingle($distributor, $query));

                //hard-coding to map outdated metadata's distribution contact to the national data center entity
                switch ($email) {
                    case 'gb-admin@ncbi.nlm.nih.gov':
                    case 'info@ncbi.nml.nih.gov':
                        $email = 'info@ncbi.nlm.nih.gov';
                        break;
                    case 'nodc.services@noaa.gov':
                        $email = 'ncei.info@noaa.gov';
                        break;
                    case 'mg-rast@rt.mcs.anl.gov':
                        $email = 'mg-rast@mcs.anl.gov';
                        break;
                    case 'nodc.services@noaa.gov':
                        $email = 'ncei.info@noaa.gov';
                        break;
                    default:
                        break;
                }

                $dataCenterArray = $em->getRepository(DataCenter::class)->findBy(
                    array('emailAddress' => $email)
                );

                if (count($dataCenterArray) > 0) {
                    $dataCenter = $dataCenterArray[0];
                } else {
                    $dataCenter = null;
                }

                // Find Role
                $query = './gmd:MD_Distributor' .
                         '/gmd:distributorContact' .
                         '/gmd:CI_ResponsibleParty' .
                         '/gmd:role' .
                         '/gmd:CI_RoleCode';

                    $roleCode = self::querySingle($distributor, $query);

                // Find Distribution URL
                $query = './gmd:MD_Distributor' .
                         '/gmd:distributorTransferOptions' .
                         '/gmd:MD_DigitalTransferOptions' .
                         '/gmd:onLine' .
                         '/gmd:CI_OnlineResource' .
                         '/gmd:linkage' .
                         '/gmd:URL';

                $distributionUrl = self::querySingle($distributor, $query);

                if ($dataCenter instanceof DataCenter) {
                    $distributionPoint = new DistributionPoint();
                    $distributionPoint->setDataCenter($dataCenter);
                    // Only set role if it is a valid role code, otherwise leave unset.
                    if (null !== $roleCode and array_key_exists($roleCode, DistributionPoint::ROLECODES)) {
                        $distributionPoint->setRoleCode($roleCode);
                    }
                    $distributionPoint->setDistributionUrl($distributionUrl);
                    $distributionPoint->setDatasetSubmission($ds);
                    $distributionPoints[] = $distributionPoint;
                }
            }
        }
        return $distributionPoints;
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
                 '/gco:CharacterString';

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
                 '/gco:CharacterString';

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
                 '/gco:CharacterString';

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
                 '/gco:CharacterString';

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
                 '/gco:CharacterString';

        return self::getDelimitedItem(self::querySingle($xml, $query), 0);
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
                 '/gco:CharacterString';

        return self::getDelimitedItem(self::querySingle($xml, $query), 1);
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
                 '/gco:CharacterString';

        return self::getDelimitedItem(self::querySingle($xml, $query), 2);
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
                 '/gco:CharacterString';


        return self::getDelimitedItem(self::querySingle($xml, $query), 3);
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
                 '/gco:CharacterString';

        return self::getDelimitedItem(self::querySingle($xml, $query), 4);
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
                 '/gco:CharacterString';

        return self::getDelimitedItem(self::querySingle($xml, $query), 5);
    }

    /**
     * Extracts reference date from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
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
     * @param \SimpleXmlElement $xml The XML to extract from.
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
     * @param \SimpleXmlElement $xml The XML to extract from.
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
                 '/gco:CharacterString';

        return self::queryMultiple($xml, $query);
    }

    /**
     * Extracts place keywords from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
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
                 '/gco:CharacterString';

        return self::queryMultiple($xml, $query);
    }

    /**
     * Extracts topic keywords from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return array Returns the topic keywords as an array, or empty array.
     */
    protected static function extractTopicKeywords(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:topicCategory' .
                 '/gmd:MD_TopicCategoryCode';

        return self::queryMultiple($xml, $query);
    }

    /**
     * Extracts GML from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return string|null Returns the GML as a string, or null.
     */
    protected static function extractSpatialExtent(\SimpleXmlElement $xml)
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
     * Extracts spatial extent description from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return string|null Returns the spatial extent description as a string, or null.
     */
    protected static function extractSpatialExtentDescription(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
                 '/gmd:identificationInfo' .
                 '/gmd:MD_DataIdentification' .
                 '/gmd:extent' .
                 '/gmd:EX_Extent' .
                 '/gmd:description' .
                 '/gco:CharacterString';

        return self::querySingle($xml, $query);
    }

    /**
     * Extracts temporal extent description from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
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
                 '/gml:description';

        $temporalExtentDescription = self::querySingle($xml, $query);
        $groundCondition = preg_match('/ground.*condition/i', $temporalExtentDescription);
        $modeledPeriod = preg_match('/modeled.*period/i', $temporalExtentDescription);

        if (1 === $groundCondition and 1 === $modeledPeriod) {
            return 'ground condition and modeled period';
        }
        if (1 === $groundCondition) {
            return 'ground condition';
        }
        if (1 === $modeledPeriod) {
            return 'modeled period';
        }
        return $temporalExtentDescription;
    }

    /**
     * Extracts temporal begin position from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
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
                 '/gml:beginPosition';

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
     * @param \SimpleXmlElement $xml The XML to extract from.
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
                 '/gml:endPosition';

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
     * @param \SimpleXmlElement $xml The XML to extract from.
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
                 '/gco:CharacterString';

        return self::querySingle($xml, $query);
    }

    /**
     * Extracts archive format from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
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
                 '/gco:CharacterString';

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
        $query = @$xml->xpath($xpath);

        if (false === $query) {
            // This is a best effort, so null if xpath fails.
            return null;
        }

        if (count($query) > 0) {
            // get first value as string.
            $value = (string) $query[0];
            if (empty($value)) {
                return null;
            }
            // remove new lines
            $value = trim(preg_replace('/\s+/', ' ', $value));

            //replace xml escape chars
            while (preg_match_all('/\&(amp|quot|lt|gt|#039|apos)\;/', $value)) {
                $value = htmlspecialchars_decode($value, (ENT_QUOTES | ENT_XML1));
            }

            return $value;
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
        $query = @$xml->xpath($xpath);

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
        $query = @$xml->xpath($xpath);

        if (false === $query) {
            // This is a best effort, so empty array if xpath fails.
            return array();
        }

        $arrayOfStrings = array();
        // Cast results to string.
        foreach ($query as $key => $value) {
            if (!empty($value)) {
                $arrayOfStrings[$key] = (string) $value;
            }
        }
        return $arrayOfStrings;
    }

    /**
     * Picks an item from a bar delimited list.
     *
     * @param string  $list   A bar delimited list of strings.
     * @param integer $offset The array offset of the desired position in the list.
     *
     * @return string|null The item at the given offset, or null.
     */
    private static function getDelimitedItem($list, $offset)
    {
        // If null input given for list, return null.
        if (null === $list) {
            return null;
        } else {
            if (preg_match('/^.*\|.*\|.*\|.*\|.*$/', $list)) {
                $items = explode('|', $list);

                if (!array_key_exists($offset, $items)) {
                    return null;
                }
            } else {
                // cannot parse
                return null;
            }

            $item = $items[$offset];
            // If blank at given position, return null, otherwise return the item.
            if (empty($item)) {
                return null;
            } else {
                return $item;
            }
        }
    }

    /**
     * Extracts temporal nilReason from XML metadata.
     *
     * @param \SimpleXmlElement $xml The XML to extract from.
     *
     * @return string|null Returns the temporal nilReason as a string, or null.
     */
    protected static function extractTemporalExtentNilReasonType(\SimpleXmlElement $xml)
    {
        $query = '/gmi:MI_Metadata' .
            '/gmd:identificationInfo' .
            '/gmd:MD_DataIdentification' .
            '/gmd:extent' .
            '/gmd:EX_Extent' .
            '/gmd:temporalElement' .
            '/@gco:nilReason';

        $queryXpath = @$xml->xpath($query);

        if (!empty($queryXpath) and is_array($queryXpath)) {
            $temporalExtentNilReason = self::getXmlAttribute($queryXpath[0], 'nilReason');
            $value = trim(preg_replace('/\s+/', ' ', $temporalExtentNilReason));
            return $value;
        }
        // This is a best effort, so null if xpath fails.
        return null;
    }

    /**
     * Static function to get the XML attribute from the SimpleXmlElement object.
     *
     * @param \SimpleXMLElement $xmlObject The Xml object from the query.
     * @param string            $attribute The attribute needed to be extracted.
     *
     * @return null|string
     */
    private static function getXmlAttribute(\SimpleXMLElement $xmlObject, $attribute)
    {
        if (isset($xmlObject[$attribute])) {
            return (string) $xmlObject[$attribute];
        }

        return null;
    }
}

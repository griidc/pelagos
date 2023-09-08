<?php

namespace App\Tests\Util;

use App\Entity\DataCenter;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DistributionPoint;
use App\Entity\Entity;
use App\Entity\File;
use App\Entity\Fileset;
use App\Entity\Person;
use App\Entity\PersonDatasetSubmission;
use App\Entity\PersonDatasetSubmissionDatasetContact;
use App\Entity\PersonDatasetSubmissionMetadataContact;
use App\Util\ISOMetadataExtractorUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Util\ISOMetadataExtractorUtilTest.
 */
class ISOMetadataExtractorUtilTest extends TestCase
{
    /**
     * A \SimpleXml object used in testing.
     *
     * @var \SimpleXmlElement
     */
    protected $xml;

    /**
     * A DatasetSubmission object used for testing.
     *
     * @var DatasetSubmission
     */
    protected $datasetSubmission;

    /**
     * Holds a ISOMetadataExtractorUtil class under test.
     *
     * @var ISOMetadataExtractorUtil
     */
    protected $util;

    /**
     * A spatial extent used for testing.
     *
     * @var string
     */
    protected $testSpatialExtent = '<gml:Polygon srsName="urn:ogc:def:crs:EPSG::4326" gml:id="Polygon1">
                                    <gml:exterior>
                                        <gml:LinearRing>
                                            <gml:posList srsDimension="2">27.68408 -97.21986 27.71022 -97.30019 27.72724 -97.34757 27.74973 -97.37366 27.76309 -97.37641 27.77767 -97.39014 27.8044 -97.3922 27.82019 -97.3874 27.84448 -97.36954 27.86269 -97.33384 27.87908 -97.28371 27.87969 -97.26174 27.87301 -97.24938 27.85662 -97.23977 27.85116 -97.23496 27.85055 -97.23084 27.8372 -97.22329 27.82384 -97.21848 27.8208 -97.18896 27.69016 -97.19308 27.68772 -97.20612 27.68651 -97.21505 27.68408 -97.21986</gml:posList>
                                        </gml:LinearRing>
                                    </gml:exterior>
                                </gml:Polygon>';

    /**
     * Holds a mock entity manager instance that matches a person.
     *
     * @var EntityManager
     */
    protected $mockEntityManager;

    /**
     * Holds a mock entity manager instance that doesn't match a person.
     *
     * @var EntityManager
     */
    protected $mockEntityManagerNoMatch;

    /**
     * Holds a Mock Person instance.
     *
     * @var Person
     */
    protected $mockPerson;

    /**
     * Holds a Mock PersonDatasetSubmissionDatasetContact.
     *
     * @var PersonDatasetSubmissionDatasetContact
     */
    protected $mockPersonDatasetSubmissionDatasetContact;

    /**
     * Holds a Mock PersonDatasetSubmissionMetadataContact.
     *
     * @var PersonDatasetSubmissionMetadataContact
     */
    protected $mockPersonDatasetSubmissionMetadataContact;

    /**
     * Holds a Mock dataset.
     *
     * @var Dataset
     */
    protected $mockDataset;

    /**
     * Holds a Mock DistributionPoint.
     *
     * @var DistributionPoint
     */
    protected $mockDistributionPoint;

    /**
     * Holds a Mock dataset.
     *
     * @var Dataset
     */
    protected $mockDatasetSubmission;

    /**
     * Holds a Datetime for testing.
     *
     * @var \DateTime
     */
    protected $testingDatetime;

    /**
     * Mock object for File entity instance.
     *
     * @var File
     */
    protected $mockFile;

    /**
     * Mock object for DataCenter.
     *
     * @var DataCenter
     */
    protected $mockDataCenter;

    /**
     * Mock object for Fileset entity instance.
     *
     * @var Fileset
     */
    protected $mockFileset;

    /**
     * Mock object for Fileset entity instance.
     *
     * @var Entity
     */
    protected $mockEntityManagerUnknownPerson;

    /**
     * The directory that contains the test data.
     *
     * @var string
     */
    protected $testDataDir = __DIR__ . '/../../_data/';

    /**
     * Unit test setup.
     *
     * @return void
     */
    public function setUp()
    {
        $this->mockPerson = \Mockery::mock(
            'App\Entity\Person',
            [
                'getEmailAddress' => 'blah@blah.com',
                'getFirstName' => 'Mock',
                'getLastName' => 'Person',
            ]
        );

        $this->mockPersonDatasetSubmissionDatasetContact = \Mockery::mock(
            'App\Entity\PersonDatasetSubmissionDatasetContact',
            [
                'getRole' => array_keys(PersonDatasetSubmission::ROLES)[0],
                'getPerson' => $this->mockPerson,
                'getId' => 8675309,
                'isPrimaryContact' => true,
            ]
        );

        $this->mockPersonDatasetSubmissionMetadataContact = \Mockery::mock(
            'App\Entity\mockPersonDatasetSubmissionMetadataContact',
            [
                'getRole' => array_keys(PersonDatasetSubmission::ROLES)[0],
                'getPerson' => $this->mockPerson,
                'getId' => 8675309,
            ]
        );

        $this->mockFile = \Mockery::mock(
            File::class,
            [
                'setFileset' => \Mockery::mock(Fileset::class),
                'setFilePathName' => 'foobar.baz',
                'getFilePathName' => 'foobar.baz',
                'setFileSize' => 1234,
                'getFileSize' => 1234,
                'setFileSha256Hash' => 'cafe',
                'getFileSha256Hash' => 'cafe',
                'getUploadedAt' => null,
                'setUploadedAt' => null,
                'getUploadedBy' => $this->mockPerson,
                'setUploadedBy' => null,
                'getDescription' => 'blah',
                'setDescription' => 'blah',
                'getPhysicalFilePath' => 'path/to/file',
                'setPhysicalFilePath' => null,
                'getStatus' => File::FILE_DONE,
                'setStatus' => FILE::FILE_DONE,
            ]
        );

        $this->mockFileset = \Mockery::mock(
            Fileset::class,
            [
                'getAllFiles' => new ArrayCollection([$this->mockFile]),
                'getZipFilePath' => '/path/to/zip',
                'getZipFileSha256Hash' => 'cfsdaf',
                'getZipFileSize' => '32432324',
                'doesZipFileExist' => true,
            ]
        );

        $this->mockDataset = \Mockery::mock(
            'App\Entity\Dataset',
            [
                'updateAvailabilityStatus' => null,
                'updateDoi' => null,
                'setDatasetStatus' => null,
                'getDatasetSubmissionHistory' => \Mockery::mock(
                    'App\Entity\DatasetSubmission',
                    [
                        'first' => \Mockery::mock(
                            'App\Entity\DatasetSubmission',
                            [
                                'getDataset' => $this->mockDataset,
                                'getSequence' => 2012,
                                'getTitle' => 'title from mock dataset submission',
                                'getShortTitle' => 'short title from mock dataset submission',
                                'getAbstract' => 'abstract from mock dataset submission',
                                'getAuthors' => 'authors from mock dataset submission',
                                'getRestrictions' => 'author only',
                                'getDoi' => 'AZ.x012.3456:7890',
                                'getDatasetFileTransferType' => 'transfer type from mock dataset submission',
                                'getDatasetFileUri' => 'uri from mock dataset submission',
                                'getLargeFileUri' => 'large file uri from mock dataset submission',
                                'getDatasetFileTransferStatus' => 'status from mock dataset submission',
                                'getDatasetFileName' => 'dataset file name from mock dataset submission',
                                'getDatasetFileSize' => '12345',
                                'getDatasetFileSha256Hash' => '131f95c51cc819465fa1797f6ccacf9d494aaaff46fa3eac73ae63ffbdfd8267',
                                'getDatasetStatus' => 'metadata status from mock dataset submission',
                                'getPurpose' => 'purpose from mock dataset submission',
                                'getSuppParams' => 'SuppParams from mock dataset submission',
                                'getSuppMethods' => 'SuppMethods from mock dataset submission',
                                'getSuppInstruments' => 'SuppInstruments from mock dataset submission',
                                'getSuppSampScalesRates' => 'SuppSampScalesRates from mock dataset submission',
                                'getSuppErrorAnalysis' => 'SuppErrorAnalysis from mock dataset submission',
                                'getSuppProvenance' => 'SuppProvenance from mock dataset submission',
                                'getThemeKeywords' => ['theme', 'keywords', 'from', 'mock', 'dataset'],
                                'getPlaceKeywords' => ['place', 'keywords', 'from', 'mock', 'dataset'],
                                'getTopicKeywords' => ['oceans', 'biota'],
                                'getSpatialExtent' => 'spatial extent from mock dataset submission',
                                'getSpatialExtentDescription' => 'spatial extent description from mock dataset submission',
                                'getTemporalExtentDesc' => 'ground condition and modeled period',
                                'getTemporalExtentBeginPosition' => $this->testingDatetime,
                                'getTemporalExtentEndPosition' => $this->testingDatetime,
                                'getTemporalExtentNilReasonType' => 'unknown',
                                'getDistributionFormatName' => 'DistributionFormatName from mock dataset submission',
                                'getFileDecompressionTechnique' => 'zip',
                                'getPrimaryDatasetContact' => $this->mockPersonDatasetSubmissionDatasetContact,
                                'getDatasetContacts' => new ArrayCollection([$this->mockPersonDatasetSubmissionDatasetContact]),
                                'getSubmitter' => $this->mockPerson,
                                'getSubmissionTimeStamp' => $this->testingDatetime,
                                'getMetadataContacts' => new ArrayCollection([$this->mockPersonDatasetSubmissionMetadataContact]),
                                'getDistributionPoints' => new ArrayCollection([\Mockery::mock(
                                    'App\Entity\DistributionPoint',
                                    [
                                        'getId' => 2222,
                                        'getRoleCode' => 'distributor',
                                        'getDataCenter' => \Mockery::mock(
                                            'App\Entity\DataCenter',
                                            [
                                                'getId' => 1234,
                                                'getOrganizationName' => 'testOrgName',
                                                'getOrganizationUrl' => 'testOrgUrl',
                                            ]
                                        ),
                                        'getDistributionUrl' => 'testDistributionUrl',
                                    ]
                                )]),
                                'getErddapUrl' => 'https://xyz',
                                'getRemotelyHostedName' => 'remote name',
                                'getRemotelyHostedDescription' => 'remote description',
                                'getRemotelyHostedFunction' => 'download',
                                'getDatasetLinks' => new ArrayCollection(),
                                'getFileset' => $this->mockFileset,
                                'getRemotelyHostedUrl' => '/path/to',
                                'isRemotelyHosted' => false,
                            ]
                        ),
                    ]
                ),
            ]
        );

        $this->mockDataCenter = \Mockery::mock(
            'App\Entity\DataCenter',
            [
                'getId' => 1234,
                'getOrganizationName' => 'testOrgName',
                'getOrganizationUrl' => 'testOrgUrl',
            ]
        );

        $this->mockDistributionPoint = \Mockery::mock(
            'App\Entity\DistributionPoint',
            [
                'getId' => 2222,
                'getRoleCode' => 'distributor',
                'getDataCenter' => $this->mockDataCenter,
                'getDistributionUrl' => 'testDistributionUrl',
            ]
        );

        $this->testingDatetime = new \DateTime();

        $this->mockDatasetSubmission = \Mockery::mock(
            'App\Entity\DatasetSubmission',
            [
                'getDataset' => $this->mockDataset,
                'getSequence' => 2012,
                'getTitle' => 'title from mock dataset submission',
                'getShortTitle' => 'short title from mock dataset submission',
                'getAbstract' => 'abstract from mock dataset submission',
                'getAuthors' => 'authors from mock dataset submission',
                'getRestrictions' => 'author only',
                'getDoi' => 'AZ.x012.3456:7890',
                'getDatasetFileTransferType' => 'transfer type from mock dataset submission',
                'getDatasetFileUri' => 'uri from mock dataset submission',
                'getLargeFileUri' => 'large file uri from mock dataset submission',
                'getDatasetFileTransferStatus' => 'status from mock dataset submission',
                'getDatasetFileName' => 'dataset file name from mock dataset submission',
                'getDatasetFileSize' => '12345',
                'getDatasetFileSha256Hash' => '131f95c51cc819465fa1797f6ccacf9d494aaaff46fa3eac73ae63ffbdfd8267',
                'getDatasetFileColdStorageArchiveSize' => 42,
                'getDatasetFileColdStorageArchiveSha256Hash' => '231f95c51cc819465fa1797f6ccacf9d494aaaff46fa3eac73ae63ffbdfd8268',
                'getDatasetStatus' => 'metadata status from mock dataset submission',
                'getPurpose' => 'purpose from mock dataset submission',
                'getSuppParams' => 'SuppParams from mock dataset submission',
                'getSuppMethods' => 'SuppMethods from mock dataset submission',
                'getSuppInstruments' => 'SuppInstruments from mock dataset submission',
                'getSuppSampScalesRates' => 'SuppSampScalesRates from mock dataset submission',
                'getSuppErrorAnalysis' => 'SuppErrorAnalysis from mock dataset submission',
                'getSuppProvenance' => 'SuppProvenance from mock dataset submission',
                'getThemeKeywords' => ['theme', 'keywords', 'from', 'mock', 'dataset'],
                'getPlaceKeywords' => ['place', 'keywords', 'from', 'mock', 'dataset'],
                'getTopicKeywords' => ['oceans', 'biota'],
                'getSpatialExtent' => 'spatial extent from mock dataset submission',
                'getSpatialExtentDescription' => 'spatial extent description from mock dataset submission',
                'getTemporalExtentDesc' => 'ground condition and modeled period',
                'getTemporalExtentBeginPosition' => $this->testingDatetime,
                'getTemporalExtentEndPosition' => $this->testingDatetime,
                'getTemporalExtentNilReasonType' => 'unknown',
                'getDistributionFormatName' => 'DistributionFormatName from mock dataset submission',
                'getFileDecompressionTechnique' => 'zip',
                'getPrimaryDatasetContact' => $this->mockPersonDatasetSubmissionDatasetContact,
                'getDatasetContacts' => new ArrayCollection([$this->mockPersonDatasetSubmissionDatasetContact]),
                'getSubmitter' => $this->mockPerson,
                'getSubmissionTimeStamp' => $this->testingDatetime,
                'getMetadataContacts' => new ArrayCollection([$this->mockPersonDatasetSubmissionMetadataContact]),
                'getDistributionPoints' => new ArrayCollection([$this->mockDistributionPoint]),
                'getErddapUrl' => 'https://xyz',
                'getRemotelyHostedName' => 'remote name',
                'getRemotelyHostedDescription' => 'remote description',
                'getRemotelyHostedFunction' => 'download',
                'getDatasetFileUrlLastCheckedDate' => new \DateTime('now', new \DateTimeZone('UTC')),
                'getDatasetFileUrlStatusCode' => '200',
                'getDatasetFileColdStorageOriginalFilename' => 'filename.txt',
                'getColdStorageTotalUnpackedCount' => '10',
                'getColdStorageTotalUnpackedSize' => '9999',
                'getDatasetLinks' => new ArrayCollection(),
                'getFileset' => $this->mockFileset,
                'getRemotelyHostedUrl' => '/path/to',
                'getAdditionalFunders' => null,
                'getKeywords' => new ArrayCollection(),
                'isRemotelyHosted' => false,
            ]
        );

        $this->mockEntityManager = \Mockery::mock(
            EntityManager::class,
            [
                'getRepository' => \Mockery::mock(
                    EntityRepository::class,
                    [
                        'findBy' => [$this->mockPerson],
                    ]
                ),
            ]
        );

        $this->mockEntityManagerUnknownPerson = \Mockery::mock(
            EntityManager::class,
            [
                'getRepository' => \Mockery::mock(
                    EntityRepository::class,
                    [
                        'findBy' => null,
                    ]
                ),
            ]
        );

        $this->mockEntityManagerNoMatch = \Mockery::mock(
            EntityManager::class,
            [
                'getRepository' => \Mockery::mock(
                    EntityRepository::class,
                    [
                        'findBy' => [],
                    ]
                ),
            ]
        );

        $this->util = new ISOMetadataExtractorUtil();

        $this->datasetSubmission = new DatasetSubmission($this->mockDatasetSubmission);
    }

    /**
     * Tests the populateDatasetSubmissionWithXMLValues method.
     *
     * @return void
     */
    public function testPopulateDatasetSubmissionWithXMLValues()
    {
        $this->util->populateDatasetSubmissionWithXMLValues(
            simplexml_load_file($this->testDataDir . 'test-metadata.xml'),
            $this->datasetSubmission,
            $this->mockEntityManager
        );

        $this->assertEquals('Test title', $this->datasetSubmission->getTitle());
        $this->assertEquals('tst ttl', $this->datasetSubmission->getShortTitle());
        $this->assertEquals('Test abstract', $this->datasetSubmission->getAbstract());
        $this->assertEquals('Test purpose', $this->datasetSubmission->getPurpose());
        $this->assertEquals('test parameter', $this->datasetSubmission->getSuppParams());
        $this->assertEquals('test method', $this->datasetSubmission->getSuppMethods());
        $this->assertEquals('test instrument', $this->datasetSubmission->getSuppInstruments());
        $this->assertEquals('test scale', $this->datasetSubmission->getSuppSampScalesRates());
        $this->assertEquals('test error', $this->datasetSubmission->getSuppErrorAnalysis());
        $this->assertEquals('test provenance', $this->datasetSubmission->getSuppProvenance());
        $this->assertEquals(['test keyword 1', 'test keyword 2', 'test keyword 3'], $this->datasetSubmission->getThemeKeywords());
        $this->assertEquals(['test place 1', 'test place 2', 'test place 3'], $this->datasetSubmission->getPlaceKeywords());
        $this->assertEquals(['oceans', 'economy'], $this->datasetSubmission->getTopicKeywords());
        $this->assertEquals($this->testSpatialExtent, $this->datasetSubmission->getSpatialExtent());
        $this->assertEquals('ground condition', $this->datasetSubmission->getTemporalExtentDesc());
        $this->assertEquals(new \DateTime('2016-10-21', new \DateTimeZone('UTC')), $this->datasetSubmission->getTemporalExtentBeginPosition());
        $this->assertEquals(new \DateTime('2016-10-22', new \DateTimeZone('UTC')), $this->datasetSubmission->getTemporalExtentEndPosition());
        $this->assertEquals('test format', $this->datasetSubmission->getDistributionFormatName());
        $this->assertEquals('test compression', $this->datasetSubmission->getFileDecompressionTechnique());
        $this->assertEquals($this->mockPerson, $this->datasetSubmission->getDatasetContacts()[0]->getPerson());

        $this->util->populateDatasetSubmissionWithXMLValues(
            simplexml_load_file($this->testDataDir . 'test-metadata-modeledperiod.xml'),
            $this->datasetSubmission,
            $this->mockEntityManager
        );

        $this->assertEquals('modeled period', $this->datasetSubmission->getTemporalExtentDesc());

        $this->util->populateDatasetSubmissionWithXMLValues(
            simplexml_load_file($this->testDataDir . 'test-metadata-groundcondition-modeledperiod.xml'),
            $this->datasetSubmission,
            $this->mockEntityManager
        );

        $this->assertEquals('ground condition and modeled period', $this->datasetSubmission->getTemporalExtentDesc());
    }

    /**
     * Tests testPopulateDatasetSubmission with xml containing an empty GML section.
     *
     * @return void
     */
    public function testPopulateDatasetSubmissionEmptyGml()
    {
        $this->util->populateDatasetSubmissionWithXMLValues(
            simplexml_load_file($this->testDataDir . 'test-metadata-empty-gml.xml'),
            $this->datasetSubmission,
            $this->mockEntityManager
        );

        $this->assertEquals('spatial extent from mock dataset submission', $this->datasetSubmission->getSpatialExtent());
    }

    /**
     * Tests the populateDatasetSubmissionWithXMLValues with xml that contains empty values.
     *
     * @return void
     */
    public function testPopulateDatasetSubmissionWithXMLValuesEmptyVals()
    {
        $this->util->populateDatasetSubmissionWithXMLValues(
            simplexml_load_file($this->testDataDir . 'test-metadata-empty-vals.xml'),
            $this->datasetSubmission,
            $this->mockEntityManagerNoMatch
        );

        $this->assertInitialValuesOfNewDatasetSubmissionFromSubmission();
    }

    /**
     * Tests the populateDatasetSubmissionWithXMLValues with xml that has bad delimeters for supplemental info.
     *
     * @return void
     */
    public function testPopulateDatasetSubmissionWithXMLValuesEmptyValsBadDelimiters()
    {
        $this->util->populateDatasetSubmissionWithXMLValues(
            simplexml_load_file($this->testDataDir . 'test-metadata-bad-delimiters.xml'),
            $this->datasetSubmission,
            $this->mockEntityManagerNoMatch
        );

        $this->assertEquals('SuppParams from mock dataset submission', $this->datasetSubmission->getSuppParams());
        $this->assertEquals('SuppMethods from mock dataset submission', $this->datasetSubmission->getSuppMethods());
        $this->assertEquals('SuppInstruments from mock dataset submission', $this->datasetSubmission->getSuppInstruments());
        $this->assertEquals('SuppSampScalesRates from mock dataset submission', $this->datasetSubmission->getSuppSampScalesRates());
        $this->assertEquals('SuppErrorAnalysis from mock dataset submission', $this->datasetSubmission->getSuppErrorAnalysis());
        $this->assertEquals('SuppProvenance from mock dataset submission', $this->datasetSubmission->getSuppProvenance());
    }

    /**
     * Tests the populateDatasetSubmissionWithXMLValues with empty ISO xml.
     *
     * @return void
     */
    public function testPopulateDatasetSubmissionWithEmptyISOXML()
    {
        $this->util->populateDatasetSubmissionWithXMLValues(
            simplexml_load_file($this->testDataDir . 'test-metadata-empty-iso.xml'),
            $this->datasetSubmission,
            $this->mockEntityManagerNoMatch
        );

        $this->assertInitialValuesOfNewDatasetSubmissionFromSubmission();
    }

    /**
     * Tests the populateDatasetSubmissionWithXMLValues with xml that is well-forme, but non-ISO.
     *
     * @return void
     */
    public function testPopulateDatasetSubmissionWithXMLValuesWellFormedNonIsoXML()
    {
        $this->util->populateDatasetSubmissionWithXMLValues(
            simplexml_load_file($this->testDataDir . 'test-metadata-well-formed-non-iso.xml'),
            $this->datasetSubmission,
            $this->mockEntityManagerNoMatch
        );
        $this->assertInitialValuesOfNewDatasetSubmissionFromSubmission();
    }

    /**
     * Assert that all metadata properties of $this->datasetSubmission match the values in the mock dataset submission.
     *
     * @return void
     */
    protected function assertInitialValuesOfNewDatasetSubmissionFromSubmission()
    {
        $this->assertEquals(
            'title from mock dataset submission',
            $this->datasetSubmission->getTitle()
        );
        $this->assertEquals(
            'short title from mock dataset submission',
            $this->datasetSubmission->getShortTitle()
        );
        $this->assertEquals(
            'abstract from mock dataset submission',
            $this->datasetSubmission->getAbstract()
        );
        $this->assertEquals(
            'purpose from mock dataset submission',
            $this->datasetSubmission->getPurpose()
        );
        $this->assertEquals(
            'SuppParams from mock dataset submission',
            $this->datasetSubmission->getSuppParams()
        );
        $this->assertEquals(
            'SuppMethods from mock dataset submission',
            $this->datasetSubmission->getSuppMethods()
        );
        $this->assertEquals(
            'SuppInstruments from mock dataset submission',
            $this->datasetSubmission->getSuppInstruments()
        );
        $this->assertEquals(
            'SuppSampScalesRates from mock dataset submission',
            $this->datasetSubmission->getSuppSampScalesRates()
        );
        $this->assertEquals(
            'SuppErrorAnalysis from mock dataset submission',
            $this->datasetSubmission->getSuppErrorAnalysis()
        );
        $this->assertEquals(
            'SuppProvenance from mock dataset submission',
            $this->datasetSubmission->getSuppProvenance()
        );
        $this->assertEquals(
            ['theme', 'keywords', 'from', 'mock', 'dataset'],
            $this->datasetSubmission->getThemeKeywords()
        );
        $this->assertEquals(
            ['place', 'keywords', 'from', 'mock', 'dataset'],
            $this->datasetSubmission->getPlaceKeywords()
        );
        $this->assertEquals(
            ['oceans', 'biota'],
            $this->datasetSubmission->getTopicKeywords()
        );
        $this->assertEquals(
            'spatial extent from mock dataset submission',
            $this->datasetSubmission->getSpatialExtent()
        );
        $this->assertEquals(
            'ground condition and modeled period',
            $this->datasetSubmission->getTemporalExtentDesc()
        );
        $this->assertEquals(
            $this->testingDatetime,
            $this->datasetSubmission->getTemporalExtentBeginPosition()
        );
        $this->assertEquals(
            $this->testingDatetime,
            $this->datasetSubmission->getTemporalExtentEndPosition()
        );
        $this->assertEquals(
            'DistributionFormatName from mock dataset submission',
            $this->datasetSubmission->getDistributionFormatName()
        );
        $this->assertEquals(
            'zip',
            $this->datasetSubmission->getFileDecompressionTechnique()
        );
        $this->assertEquals(
            $this->mockPersonDatasetSubmissionDatasetContact->getPerson(),
            $this->datasetSubmission->getDatasetContacts()->first()->getPerson()
        );
    }

    /**
     * Test the extractPointsOfContact method that extracts PersonDatasetSubmissionDatasetContacts from XML.
     *
     * @return void
     */
    public function testExtractPointsOfContact()
    {
        $this->xml = simplexml_load_file($this->testDataDir . 'test-multi-contacts.xml');

        $contacts = ISOMetadataExtractorUtil::extractPointsOfContact($this->xml, $this->datasetSubmission, $this->mockEntityManager);

        $this->assertInstanceOf(PersonDatasetSubmissionDatasetContact::class, $contacts[0]);
        $this->assertEquals(
            'Mock',
            $contacts[0]->getPerson()->getFirstName()
        );
        $this->assertEquals(
            'Person',
            $contacts[0]->getPerson()->getLastName()
        );
        $this->assertEquals(
            'principalInvestigator',
            $contacts[0]->getRole()
        );

        $this->assertInstanceOf(PersonDatasetSubmissionDatasetContact::class, $contacts[1]);
        $this->assertEquals(
            'Mock',
            $contacts[1]->getPerson()->getFirstName()
        );
        $this->assertEquals(
            'Person',
            $contacts[1]->getPerson()->getLastName()
        );
        $this->assertEquals(
            'author',
            $contacts[1]->getRole()
        );
    }

    /**
     * Test to extract temporal nil reason for dataset submission.
     *
     * @return void
     */
    public function testCanExtractTemporalNilReasonFromXml()
    {
        $mockTemporalNilReason = 'unknown';

        $this->assertEquals($mockTemporalNilReason, $this->datasetSubmission->getTemporalExtentNilReasonType());
    }
}

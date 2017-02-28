<?php
namespace Tests\unit\Pelagos\Util;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Person;
use Pelagos\Util\ISOMetadataExtractorUtil;

/**
 * Unit tests for Pelagos\Util\ISOMetadataExtractorUtilTest.
 *
 * @group Pelagos
 * @group Pelagos\Util
 */
class ISOMetadataExtractorUtilTest extends \PHPUnit_Framework_TestCase
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
     * @var DatasetSubmission $datasetSubmission
     */
    protected $datasetSubmission;

    /**
     * Holds a ISOMetadataExtractorUtil class under test.
     *
     * @var Pelagos\Util\ISOMetadataExtractorUtil
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
     * The directory that contains the test data.
     *
     * @var string
     */
    protected $testDataDir = __DIR__ . '/../../../data/';

    /**
     * Unit test setup.
     *
     * @return void
     */
    public function setUp()
    {
        $this->mockPerson = \Mockery::mock(
            'Pelagos\Entity\Person',
            array(
                'getEmailAddress' => 'blah@blah.com'
            )
        );

        $this->mockEntityManager = \Mockery::mock(
            EntityManager::class,
            array(
                'getRepository' => \Mockery::mock(
                    EntityRepository::class,
                    array(
                        'findBy' => array($this->mockPerson),
                    )
                ),
            )
        );

        $this->mockEntityManagerNoMatch = \Mockery::mock(
            EntityManager::class,
            array(
                'getRepository' => \Mockery::mock(
                    EntityRepository::class,
                    array(
                        'findBy' => array(),
                    )
                ),
            )
        );

        $this->util = new ISOMetadataExtractorUtil;
        // I really should be mocking this, but I really need it to remember what it set, so I'm not.
        $this->datasetSubmission = new DatasetSubmission;
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
        $this->assertEquals(new \DateTime('2016-10-21', new \DateTimeZone('UTC')), $this->datasetSubmission->getReferenceDate());
        $this->assertEquals('publication', $this->datasetSubmission->getReferenceDateType());
        $this->assertEquals(array('test keyword 1', 'test keyword 2', 'test keyword 3'), $this->datasetSubmission->getThemeKeywords());
        $this->assertEquals(array('test place 1', 'test place 2', 'test place 3'), $this->datasetSubmission->getPlaceKeywords());
        $this->assertEquals(array('oceans', 'economy'), $this->datasetSubmission->getTopicKeywords());
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

        $this->assertNull($this->datasetSubmission->getSpatialExtent());
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

        $this->assertAllNullOrEmpty();
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

        $this->assertEquals(
            'test parameter,test method,test instrument,test scale,test error,test provenance',
            $this->datasetSubmission->getSuppParams()
        );
        $this->assertNull($this->datasetSubmission->getSuppMethods());
        $this->assertNull($this->datasetSubmission->getSuppInstruments());
        $this->assertNull($this->datasetSubmission->getSuppSampScalesRates());
        $this->assertNull($this->datasetSubmission->getSuppErrorAnalysis());
        $this->assertNull($this->datasetSubmission->getSuppProvenance());
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

        $this->assertAllNullOrEmpty();
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
        $this->assertAllNullOrEmpty();
    }

    /**
     * Assert that all metadata properties of $this->datasetSubmission are null or empty.
     *
     * @return void
     */
    protected function assertAllNullOrEmpty()
    {
        $this->assertNull($this->datasetSubmission->getTitle());
        $this->assertNull($this->datasetSubmission->getShortTitle());
        $this->assertNull($this->datasetSubmission->getAbstract());
        $this->assertNull($this->datasetSubmission->getPurpose());
        $this->assertNull($this->datasetSubmission->getSuppParams());
        $this->assertNull($this->datasetSubmission->getSuppMethods());
        $this->assertNull($this->datasetSubmission->getSuppInstruments());
        $this->assertNull($this->datasetSubmission->getSuppSampScalesRates());
        $this->assertNull($this->datasetSubmission->getSuppErrorAnalysis());
        $this->assertNull($this->datasetSubmission->getSuppProvenance());
        $this->assertNull($this->datasetSubmission->getReferenceDate());
        $this->assertNull($this->datasetSubmission->getReferenceDateType());
        $this->assertEmpty($this->datasetSubmission->getThemeKeywords());
        $this->assertEmpty($this->datasetSubmission->getPlaceKeywords());
        $this->assertEmpty($this->datasetSubmission->getTopicKeywords());
        $this->assertNull($this->datasetSubmission->getSpatialExtent());
        $this->assertNull($this->datasetSubmission->getTemporalExtentDesc());
        $this->assertNull($this->datasetSubmission->getTemporalExtentBeginPosition());
        $this->assertNull($this->datasetSubmission->getTemporalExtentEndPosition());
        $this->assertNull($this->datasetSubmission->getDistributionFormatName());
        $this->assertNull($this->datasetSubmission->getFileDecompressionTechnique());
        $this->assertEmpty($this->datasetSubmission->getDatasetContacts());
    }
}

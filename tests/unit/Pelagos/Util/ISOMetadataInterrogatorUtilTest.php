<?php

namespace Pelagos;

use Pelagos\Entity\DatasetSubmission;
use Pelagos\Util\ISOMetadataInterrogatorUtil;

/**
 * Unit tests for Pelagos\Util\ISOMetadataInterrogatorUtilTest.
 *
 * @group Pelagos
 * @group Pelagos\Util
 */
class ISOMetadataInterrogatorUtilTest extends \PHPUnit_Framework_TestCase
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
     * Holds a ISOMetadataInterrogatorUtil class under test.
     *
     * @var Pelagos\Util\ISOMetadataInterrogatorUtil
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
     * Unit test setup.
     *
     * @return void
     */
    public function setUp()
    {
        $this->util = new ISOMetadataInterrogatorUtil;
        $this->xml = simplexml_load_file(dirname(__FILE__) . '/../../../data/test-metadata.xml');
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
        $this->util->populateDatasetSubmissionWithXMLValues($this->xml, $this->datasetSubmission);

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
        $this->assertEquals('groundcondition', $this->datasetSubmission->getTemporalExtentDesc());
        $this->assertEquals(new \DateTime('2016-10-21', new \DateTimeZone('UTC')), $this->datasetSubmission->getTemporalExtentBeginPosition());
        $this->assertEquals(new \DateTime('2016-10-22', new \DateTimeZone('UTC')), $this->datasetSubmission->getTemporalExtentEndPosition());
        $this->assertEquals('test format', $this->datasetSubmission->getDistributionFormatName());
        $this->assertEquals('test compression', $this->datasetSubmission->getFileDecompressionTechnique());
        $this->assertEquals('http://test.url', $this->datasetSubmission->getDatasetFileUri());
    }
}

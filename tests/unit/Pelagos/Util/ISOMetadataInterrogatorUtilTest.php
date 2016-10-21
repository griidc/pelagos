<?php

namespace Pelagos;

use Pelagos\Entity\DatasetSubmission;
use Pelagos\Util\ISOMetadataInterrogatorUtil;

/**
 * Unit tests for Pelagos\Util\ISOMetadataInterrogatorUtilTest
 *
 * @group Pelagos
 * @group Pelagos\Util
 */
class ISOMetadataInterrogatorUtilTest extends \PHPUnit_Framework_TestCase
{
    /**
     * A \SimpleXml object used in testing.
     *
     * @var \SimpleXmlElement $xml
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
     * @var Pelagos\Util\ISOMetadataInterrogatorUtil $util
     */
    protected $util;

    public function setUp()
    {
        $this->util = new ISOMetadataInterrogatorUtil;
        $this->xml = simplexml_load_file('/home/mwilliamson/pelagos/tests/data/test-metadata.xml');
        // I really should be mocking this, but I really need it to remember what it set, so I'm not.
        $this->datasetSubmission = new DatasetSubmission;
    }

    /**
     * Tests the returnDsWithXmlPri method.
     *
     * @return void
     */
    public function testReturnDsWithXmlPri()
    {
        $this->util->returnDsWithXmlPri($this->xml, $this->datasetSubmission);

        //var_dump($this->datasetSubmission); die();

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
    }

}

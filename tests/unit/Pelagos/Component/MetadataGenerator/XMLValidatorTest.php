<?php

namespace Pelagos\Component\MetadataGenerator;

/**
 * Class to test XMLValidator.
 */
class XMLValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test good metadata.
     *
     * @return void
     */
    public function testGoodMetadata()
    {
        $validator = new XMLValidator;
        $filename = __DIR__ . "/data/good-metadata.xml";
        $fhandle = fopen($filename, "r");
        $xml = fread($fhandle, filesize($filename));
        fclose($fhandle);
        $this->assertTrue($validator->validate($xml));
    }

    /**
     * Test known bad metadata.
     *
     * @expectedException \Pelagos\Exception\InvalidXmlException
     *
     * @return void
     */
    public function testKnownBadMetadata()
    {
        $validator = new XMLValidator;
        $filename = __DIR__ . "/data/bad-metadata.xml";
        $fhandle = fopen($filename, "r");
        $xml = fread($fhandle, filesize($filename));
        fclose($fhandle);
        $validator->validate($xml);
    }

    /**
     * Test known unparseable metadata.
     *
     * @expectedException \Pelagos\Exception\InvalidXmlException
     *
     * @return void
     */
    public function testKnownUnparsableMetadata()
    {
        $validator = new XMLValidator;
        $filename = __DIR__ . "/data/unparsable-metadata.xml";
        $fhandle = fopen($filename, "r");
        $xml = fread($fhandle, filesize($filename));
        fclose($fhandle);
        $validator->validate($xml);
    }

    /**
     * Test bone head binary.
     *
     * @expectedException \Pelagos\Exception\InvalidXmlException
     *
     * @return void
     */
    public function testBoneheadBinary()
    {
        $validator = new XMLValidator;
        $filename = __DIR__ . "/data/metadata-doc.doc";
        $fhandle = fopen($filename, "r");
        $xml = fread($fhandle, filesize($filename));
        fclose($fhandle);
        $validator->validate($xml);
    }
}

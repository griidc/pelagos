<?php

namespace Pelagos\Component\MetadataGenerator;

class XMLValidatorTest extends \PHPUnit_Framework_TestCase
{
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
     * @expectedException \Pelagos\Exception\InvalidXmlException
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
     * @expectedException \Pelagos\Exception\InvalidXmlException
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
     * @expectedException \Pelagos\Exception\InvalidXmlException
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

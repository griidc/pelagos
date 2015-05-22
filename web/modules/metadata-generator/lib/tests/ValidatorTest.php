<?php

namespace MetadataGenerator;

class XMLValidatorTest extends \PHPUnit_Framework_TestCase
{
    private $validator;

    protected function setUp()
    {
        error_reporting(E_ERROR);
        require_once __DIR__.'/../Validator.php';
    }

    public function testGoodMetadata()
    {
        $validator = new XMLValidator;
        $filename = __DIR__."/data/good-metadata.xml";
        $fhandle = fopen($filename, "r");
        $xml = fread($fhandle, filesize($filename));
        fclose($fhandle);
        $this->assertTrue($validator->validate($xml));
    }

    public function testKnownBadMetadata()
    {
        $validator = new XMLValidator;
        $filename = __DIR__."/data/bad-metadata.xml";
        $fhandle = fopen($filename, "r");
        $xml = fread($fhandle, filesize($filename));
        fclose($fhandle);
        $this->assertFalse($validator->validate($xml));
    }

    public function testKnownUnparsableMetadata()
    {
        $validator = new XMLValidator;
        $filename = __DIR__."/data/unparsable-metadata.xml";
        $fhandle = fopen($filename, "r");
        $xml = fread($fhandle, filesize($filename));
        fclose($fhandle);
        $this->assertFalse($validator->validate($xml));
    }

    public function testBoneheadBinary()
    {
        $validator = new XMLValidator;
        $filename = __DIR__."/data/metadata-doc.doc";
        $fhandle = fopen($filename, "r");
        $xml = fread($fhandle, filesize($filename));
        fclose($fhandle);
        $this->assertFalse($validator->validate($xml));
    }
}

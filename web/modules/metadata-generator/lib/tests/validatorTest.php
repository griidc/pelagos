<?php

namespace XMLValidator;


class XMLValidatorTest extends \PHPUnit_Framework_TestCase
{
    private $validator;

    protected function setUp()
    {
        error_reporting(E_ERROR);
        require_once '../XMLValidator.php';
    }

    public function testGoodMetadata()
    {
        $validator = new XMLValidator;
        $filename = "data/good-metadata.xml";
        $fhandle = fopen($filename, "r");
        $xml = fread($fhandle, filesize($filename));
        fclose($fhandle);
        $this->assertTrue($validator->validate($xml));
    }

    public function testKnownBadMetadata()
    {
        $validator = new XMLValidator;
        $filename = "data/bad-metadata.xml";
        $fhandle = fopen($filename, "r");
        $xml = fread($fhandle, filesize($filename));
        fclose($fhandle);
        $this->assertFalse($validator->validate($xml));
    }

    public function testKnownUnparsableMetadata()
    {
        $validator = new XMLValidator;
        $filename = "data/unparsable-metadata.xml";
        $fhandle = fopen($filename, "r");
        $xml = fread($fhandle, filesize($filename));
        fclose($fhandle);
        $this->assertFalse($validator->validate($xml));
    }

    public function testBoneheadBinary()
    {
        $validator = new XMLValidator;
        $filename = "data/metadata-doc.doc";
        $fhandle = fopen($filename, "r");
        $xml = fread($fhandle, filesize($filename));
        fclose($fhandle);
        $this->assertFalse($validator->validate($xml));
    }
}

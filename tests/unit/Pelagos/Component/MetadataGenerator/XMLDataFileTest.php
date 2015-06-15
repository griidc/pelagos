<?php

namespace MetadataGenerator;

class XMLDataFileTest extends \PHPUnit_Framework_TestCase
{
    private $md;

    protected function setUp()
    {
        require_once __DIR__ . '/../XMLDataFile.php';
    }

    public function testKnownMetadata()
    {
        $md = new XMLDataFile;
        $udi = 'R2.x224.000:0001';
        $xmltext = $md->getXML($udi);
        $hash = md5($xmltext);
        $this->assertEquals($hash, '7daa92df762ae07755f56fdd66a32861');
    }
}

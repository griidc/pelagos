<?php

namespace App\Tests\Entity;

use App\Entity\File;
use App\Entity\InformationProduct;
use App\Entity\ResearchGroup;
use PHPUnit\Framework\TestCase;

use Symfony\Component\Validator\Validation;

/**
 * Unit tests for App\Entity\InformationProduct.
 */
class InformationProductTest extends TestCase
{

    /**
    * Holds an instance of InformationProduct class for testing.
    */
    private $infoProduct;

    public function setUp(): void
    {
        $this->infoProduct = new InformationProduct;
    }

    /**
     * Tests the title.
     *
     * @return void
     */
    public function testTitle()
    {
        $testTitle = 'Hello Title!';
        $this->infoProduct->setTitle($testTitle);
        $this->assertSame($testTitle, $this->infoProduct->getTitle());
    }

    /**
     * Tests the creators.
     *
     * @return void
     */
    public function testGetCreators()
    {
        $testCreatorList = 'Larry, Curly, Mo';
        $this->infoProduct->setCreators($testCreatorList);
        $this->assertSame($testCreatorList, $this->infoProduct->getCreators());
    }

    /**
     * Tests the publisher.
     *
     * @return void
     */
    public function testGetPublisher()
    {
        $testPublisher = 'Acme Publishing Inc.';
        $this->infoProduct->setPublisher($testPublisher);
        $this->assertSame($testPublisher, $this->infoProduct->getPublisher());
    }

    /**
     * Tests the external DOI.
     *
     * @return void
     */
    public function testGetExternalDoi()
    {
        $testDoi = '10.1234';
        $this->infoProduct->setExternalDoi($testDoi);
        $this->assertSame($testDoi, $this->infoProduct->getExternalDoi());
    }

    /**
     * Tests the published bool flag.
     *
     * @return void
     */
    public function testPublished()
    {
        $this->assertFalse($this->infoProduct->isPublished(), 'Initial state not false per declaration.');
        $this->infoProduct->setPublished(true);
        $this->assertTrue($this->infoProduct->isPublished());
    }

    /**
     * Tests the remote resource bool flag.
     *
     * @return void
     */
    public function testRemoteResource()
    {
        $this->assertFalse($this->infoProduct->getRemoteResource(), 'Initial state not false per declaration.');
        $this->infoProduct->setRemoteResource(true);
        $this->assertTrue($this->infoProduct->getRemoteResource());
    }

    public function testResearchGroups()
    {
        $testRG = new ResearchGroup;
        $testRG2 = new ResearchGroup;
        $this->assertCount(0, $this->infoProduct->getResearchGroups());
        $this->infoProduct->addResearchGroup($testRG);
        $this->assertCount(1, $this->infoProduct->getResearchGroups());
        $this->infoProduct->addResearchGroup($testRG2);
        $this->assertCount(2, $this->infoProduct->getResearchGroups());
        $this->assertEquals(array(0 => null, 1 => null), $this->infoProduct->getResearchGroupList());
        $this->infoProduct->removeResearchGroup($testRG);
        $this->assertCount(1, $this->infoProduct->getResearchGroups());
    }

    /**
     * Tests the remoteUri field.
     *
     * @return void
     */
    public function testRemoteUri()
    {
        $this->assertNull($this->infoProduct->getRemoteUri());
        $testUri = 'https://test.blah/stuff';
        $this->infoProduct->setRemoteUri($testUri);
        $this->assertSame($testUri, $this->infoProduct->getRemoteUri());
        $this->infoProduct->setRemoteUri(null);
        $this->assertNull($this->infoProduct->getRemoteUri(), 'Could not set Remote URI back to null.');
    }

    /**
     * Tests the file field.
     *
     * @return void
     */
    public function testFile()
    {
        $testFile = new File;
        $this->assertNull($this->infoProduct->getFile());
        $this->infoProduct->setFile($testFile);
        $this->assertSame($testFile, $this->infoProduct->getFile());

    }

    /**
     * Tests the remoteUri field.
     *
     * @return void
     */
    public function testGetRemoteUriHostName()
    {
        $testUri = 'https://test.blah/stuff';
        $this->infoProduct->setRemoteUri($testUri);
        $remoteUriHost = $this->infoProduct->getRemoteUriHostName();
        $this->assertSame($remoteUriHost, 'test.blah');
    }
}

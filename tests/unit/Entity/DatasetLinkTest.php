<?php

namespace App\Tests\Unit\Entity;

use App\Entity\DatasetLink;
use App\Entity\DatasetSubmission;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\DatasetLink.
 */
class DatasetLinkTest extends TestCase
{
    /**
     * The DatasetLink instance to be tested.
     */
    private DatasetLink $datasetLink;

    /**
     * A mock DatasetSubmission object.
     * @var DatasetSubmission|\Mockery\MockInterface
     */
    private $mockDatasetSubmission;

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        $this->datasetLink = new DatasetLink();
        $this->mockDatasetSubmission = \Mockery::mock(DatasetSubmission::class);
    }

    /**
     * Test the getUrl and setUrl methods.
     */
    public function testGetAndSetUrl(): void
    {
        $this->datasetLink->setUrl('http://example.com');
        $this->assertEquals('http://example.com', $this->datasetLink->getUrl());
    }

    /**
     * Test the getName and setName methods.
     */
    public function testGetAndSetName(): void
    {
        $this->datasetLink->setName('Test Link');
        $this->assertEquals('Test Link', $this->datasetLink->getName());
    }

    /**
     * Test the getDescription and setDescription methods.
     */
    public function testGetAndSetDescription(): void
    {
        $this->datasetLink->setDescription('Test description.');
        $this->assertEquals('Test description.', $this->datasetLink->getDescription());
    }

    /**
     * Test the getFunctionCode and setFunctionCode methods.
     */
    public function testGetAndSetFunctionCode(): void
    {
        $this->datasetLink->setFunctionCode('download');
        $this->assertEquals('download', $this->datasetLink->getFunctionCode());
    }

    /**
     * Test setting an invalid function code.
     */
    public function testSetInvalidFunctionCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->datasetLink->setFunctionCode('invalid-code');
    }

    /**
     * Test the getProtocol and setProtocol methods.
     */
    public function testGetAndSetProtocol(): void
    {
        $this->datasetLink->setProtocol('HTTP');
        $this->assertEquals('HTTP', $this->datasetLink->getProtocol());
    }

    /**
     * Test the getDatasetSubmission and setDatasetSubmission methods.
     */
    public function testGetAndSetDatasetSubmission(): void
    {
        $this->datasetLink->setDatasetSubmission($this->mockDatasetSubmission);
        $this->assertSame($this->mockDatasetSubmission, $this->datasetLink->getDatasetSubmission());
    }

    /**
     * Test the getFunctionCodeChoices static method.
     */
    public function testGetFunctionCodeChoices(): void
    {
        $expectedChoices = [
            'Download' => 'download',
            'Information' => 'information',
            'Offline Access' => 'offlineAccess',
            'Order' => 'order',
            'Search' => 'search',
        ];
        $this->assertEquals($expectedChoices, DatasetLink::getFunctionCodeChoices());
    }

    /**
     * Test the getLinkNameCodeChoices static method.
     */
    public function testGetLinkNameCodeChoices(): void
    {
        $expectedChoices = [
            'ERDDAP' => 'erddap',
            'NCEI' => 'ncei',
        ];
        $this->assertEquals($expectedChoices, DatasetLink::getLinkNameCodeChoices());
    }

    /**
     * Test the __toString method.
     */
    public function testToString(): void
    {
        $this->datasetLink->setName('Test Link');
        $this->datasetLink->setUrl('http://example.com');
        $this->assertEquals('Test Link (http://example.com)', (string) $this->datasetLink);
    }

    /**
     * Test the tear down of the test.
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }
}

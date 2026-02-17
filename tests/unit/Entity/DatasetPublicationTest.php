<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Dataset;
use App\Entity\DatasetPublication;
use App\Entity\Publication;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for App\Entity\DatasetPublication.
 */
class DatasetPublicationTest extends TestCase
{
    /**
     * The DatasetPublication instance to be tested.
     */
    private DatasetPublication $datasetPublication;

    /**
     * A mock Dataset object.
     * @var Dataset|\Mockery\MockInterface
     */
    private $mockDataset;

    /**
     * A mock Publication object.
     * @var Publication|\Mockery\MockInterface
     */
    private $mockPublication;

    /**
     * Test setup.
     */
    protected function setUp(): void
    {
        $this->mockDataset = \Mockery::mock(Dataset::class);
        $this->mockPublication = \Mockery::mock(Publication::class);
        $this->datasetPublication = new DatasetPublication($this->mockPublication, $this->mockDataset);
    }

    /**
     * Test the getPublication method.
     */
    public function testGetPublication(): void
    {
        $this->assertSame($this->mockPublication, $this->datasetPublication->getPublication());
    }

    /**
     * Test the getDataset method.
     */
    public function testGetDataset(): void
    {
        $this->assertSame($this->mockDataset, $this->datasetPublication->getDataset());
    }

    /**
     * Test the tear down of the test.
     */
    protected function tearDown(): void
    {
        \Mockery::close();
    }
}

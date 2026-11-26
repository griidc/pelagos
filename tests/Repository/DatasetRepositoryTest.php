<?php

namespace App\Tests\Repository;

use App\Entity\Dataset;
use App\Repository\DatasetRepository;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class DatasetRepositoryTest extends TestCase
{
    public function testFindAllAsCollectionReturnsCollection(): void
    {
        $dataset1 = $this->createMock(Dataset::class);
        $dataset2 = $this->createMock(Dataset::class);

        $repository = $this->createPartialMock(DatasetRepository::class, ['findAll']);
        $repository->method('findAll')->willReturn([$dataset1, $dataset2]);

        $result = $repository->findAllAsCollection();

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function testFindAllAsCollectionContainsDatasets(): void
    {
        $dataset1 = $this->createMock(Dataset::class);
        $dataset2 = $this->createMock(Dataset::class);

        $repository = $this->createPartialMock(DatasetRepository::class, ['findAll']);
        $repository->method('findAll')->willReturn([$dataset1, $dataset2]);

        $result = $repository->findAllAsCollection();

        $this->assertCount(2, $result);
        foreach ($result as $item) {
            $this->assertInstanceOf(Dataset::class, $item);
        }
    }

    public function testFindAllAsCollectionMatchesFindAll(): void
    {
        $dataset1 = $this->createMock(Dataset::class);
        $dataset2 = $this->createMock(Dataset::class);
        $findAllData = [$dataset1, $dataset2];

        $repository = $this->createPartialMock(DatasetRepository::class, ['findAll']);
        $repository->method('findAll')->willReturn($findAllData);

        $findAllResult = $repository->findAll();
        $findAllAsCollectionResult = $repository->findAllAsCollection();

        $this->assertSame(count($findAllResult), $findAllAsCollectionResult->count());
    }

    public function testFindAllAsCollectionWithEmptyArray(): void
    {
        $repository = $this->createPartialMock(DatasetRepository::class, ['findAll']);
        $repository->method('findAll')->willReturn([]);

        $result = $repository->findAllAsCollection();

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertCount(0, $result);
    }
}

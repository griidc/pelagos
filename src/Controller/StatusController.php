<?php

namespace App\Controller;

use App\Entity\Dataset;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Client;
use Elastica\Index;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StatusController extends AbstractController
{
    private const STATUS_TOOL_VERSION = '1.0.0';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TransformedFinder $searchPelagosFinder,
        private readonly Client $elasticaClient,
        private readonly string $expectedDatasetCountMin,
        private readonly string $indexName,
        private readonly string $storageDir,
        private readonly string $uploadDir
    ) {
    }

    /**
     * This route returns JSON status information about the application component and
     * returns an overall response code for external monitoring of aggregate system
     * health.
     *
     * @return JsonResponse
     */
    #[Route('/status', name: 'app_status')]
    public function index(): JsonResponse
    {
        $databaseStatus = $this->getDatabaseEngineStatus();
        $elasticsearchStatus = $this->getElasticStatus();
        $pelagosDatasetCount = $this->getPelagosDatasetCount();
        $fileSystemStatus = $this->testFilesystemsPaths();

        $overallStatus = $databaseStatus && $elasticsearchStatus && $pelagosDatasetCount >= $this->expectedDatasetCountMin && $fileSystemStatus ? 'ok' : 'error';

        $returnCode = $overallStatus === 'ok' ? 200 : 500;

        $status = [
            'status' => $overallStatus,
            'version' => self::STATUS_TOOL_VERSION,
            'timestamp' => (new \DateTime())->format('c'),
            'database' => $databaseStatus,
            'elasticsearch' => $elasticsearchStatus,
            'pelagosDatasetCount' => $pelagosDatasetCount,
            'fileSystems' => $fileSystemStatus
        ];

        return new JsonResponse(
            data: $status,
            status: $returnCode
        );
    }

    /**
     * Checks the database connection by executing a simple query.
     *
     * @return bool True if the database is reachable, false otherwise.
     */
    private function getDatabaseEngineStatus(): bool
    {
        try {
            $connection = $this->entityManager->getConnection();
            $connection->executeQuery('SELECT 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Gets the count of datasets in the Pelagos system.
     *
     * @return int The number of datasets.
     */
    private function getPelagosDatasetCount(): int
    {
        try {
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $count = $queryBuilder
                ->select('COUNT(dataset.id)')
                ->from(Dataset::class, 'dataset')
                ->getQuery()
                ->getSingleScalarResult();

            return (int) $count;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Checks the status of the Elasticsearch service.
     *
     * @return bool True if Elasticsearch is reachable and healthy, false otherwise.
     */
    private function getElasticStatus(): bool
    {
        try {
            $client = $this->elasticaClient;

            // Get the status of a specific index
            $index = new Index($client, $this->indexName);
            $indexStatus = $index->getStats()->getResponse()->getStatus();

            // Get cluster health
            $clusterHealth = $client->getCluster()->getHealth();
            // Get data from the cluster health object
            $clusterHealthData = $clusterHealth->getData();

            // Accessing specific data within the cluster health data:
            $status = $clusterHealthData['status']; // e.g., green, yellow, red

            return $indexStatus === 200 && ($status === 'green' || $status === 'yellow');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Test critical filesystem paths.
     */
    private function testFilesystemsPaths(): bool
    {
        try {
            if (!is_dir($this->storageDir) || !is_dir($this->uploadDir)) {
                return false;
            }

            if (!is_writable($this->uploadDir)) {
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

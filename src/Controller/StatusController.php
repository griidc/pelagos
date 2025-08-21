<?php

namespace App\Controller;

use App\Entity\Dataset;
use App\Util\ServiceStatus;
use Doctrine\Common\Collections\ArrayCollection;
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
    private const STATUS_TOOL_VERSION = '1.0.3';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TransformedFinder $searchPelagosFinder,
        private readonly Client $elasticaClient,
        private readonly int $expectedDatasetCountMin,
        private readonly string $indexName,
        private readonly string $storageDir,
        private readonly string $uploadBaseDir,
    ) {
    }

    /**
     * This route returns JSON status information about the application component and
     * returns an overall response code for external monitoring of aggregate system
     * health.
     */
    #[Route('/status', name: 'app_status')]
    public function index(): Response
    {
        $databaseStatus = $this->getDatabaseEngineStatus();
        $elasticsearchStatus = $this->getElasticStatus();
        $pelagosDatasetCount = $this->getPelagosDatasetCount();
        $fileSystemStatus = $this->testFilesystemsPaths();

        /**
         * @var ArrayCollection<array-key, ServiceStatus> $services
         */
        $services = new ArrayCollection();

        $services->add($databaseStatus);
        $services->add($elasticsearchStatus);
        $services->add($fileSystemStatus);
        $services->add($pelagosDatasetCount);

        $allServicesOk = 0 === $services->filter(function (ServiceStatus $serviceStatus) {
            return ServiceStatus::STATUS_ERROR === $serviceStatus->getStatus();
        })->count();

        $status = [
            'overallStatus' => $allServicesOk ? ServiceStatus::STATUS_OK : ServiceStatus::STATUS_ERROR,
            'version' => self::STATUS_TOOL_VERSION,
            'timestamp' => (new \DateTime())->format('c'),
            'database' => $databaseStatus->getResults(),
            'elasticsearch' => $elasticsearchStatus->getResults(),
            'pelagosDatasetCount' => $pelagosDatasetCount->getResults(),
            'fileSystems' => $fileSystemStatus->getResults(),
        ];

        return new JsonResponse(
            data: $status,
            status: $allServicesOk ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Checks the database connection by executing a simple query.
     */
    private function getDatabaseEngineStatus(): ServiceStatus
    {
        $serviceStatus = new ServiceStatus();
        try {
            $connection = $this->entityManager->getConnection();
            $connection->executeQuery('SELECT 1');
            $serviceStatus->setStatus(ServiceStatus::STATUS_OK);
            $serviceStatus->setData(['Database connection' => 'Successful']);
        } catch (\Throwable $e) {
            $serviceStatus->setThrowable($e);
        }

        return $serviceStatus;
    }

    /**
     * Gets the count of datasets in the Pelagos system.
     */
    private function getPelagosDatasetCount(): ServiceStatus
    {
        $serviceStatus = new ServiceStatus();
        try {
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $count = $queryBuilder
                ->select('COUNT(dataset.id)')
                ->from(Dataset::class, 'dataset')
                ->getQuery()
                ->getSingleScalarResult();

            $serviceStatus->setStatus(ServiceStatus::STATUS_OK);
            $serviceStatus->setData(['Number of Datasets' => (int) $count]);
        } catch (\Throwable $e) {
            $serviceStatus->setThrowable($e);
        }

        return $serviceStatus;
    }

    /**
     * Checks the status of the Elasticsearch service.
     */
    private function getElasticStatus(): ServiceStatus
    {
        $serviceStatus = new ServiceStatus();
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

            $result = [];
            $result['index'] = $indexStatus;
            $result['status'] = $status;
            $serviceStatus->setData($result);

            if (200 === $indexStatus && ('green' == $status || 'yellow' == $status)) {
                $serviceStatus->setStatus(ServiceStatus::STATUS_OK);
            }
        } catch (\Throwable $e) {
            $serviceStatus->setThrowable($e);
        }

        return $serviceStatus;
    }

    /**
     * Test critical filesystem paths.
     */
    private function testFilesystemsPaths(): ServiceStatus
    {
        $serviceStatus = new ServiceStatus();
        try {
            $uploadDirectory = $this->uploadBaseDir . '/upload';
            if (!is_dir($this->storageDir)) {
                $serviceStatus->setStatus(ServiceStatus::STATUS_ERROR);
                $serviceStatus->setData(['error' => "Required storage directory is missing: {$this->storageDir}"]);
            } else {
                $serviceStatus->setData(['info' => "Storage directory is present: {$this->storageDir}"]);
            }

            if (!is_dir($uploadDirectory)) {
                $serviceStatus->setStatus(ServiceStatus::STATUS_ERROR);
                $serviceStatus->setData(['error' => "Required upload directory is missing: {$uploadDirectory}"]);
            } else {
                $serviceStatus->setData(['info' => "Upload directory is present: {$uploadDirectory}"]);
            }

            if (!is_writable($uploadDirectory)) {
                $serviceStatus->setStatus(ServiceStatus::STATUS_ERROR);
                $serviceStatus->setData(['error' => "Upload directory is not writable: {$uploadDirectory}"]);
            } else {
                $serviceStatus->setData(['info' => "Upload directory is writable: {$uploadDirectory}"]);
            }
        } catch (\Throwable $e) {
            $serviceStatus->setThrowable($e);
        }

        return $serviceStatus;
    }
}

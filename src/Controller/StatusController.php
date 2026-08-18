<?php

namespace App\Controller;

use App\Entity\Dataset;
use App\Util\ServiceStatus;
use App\Util\ServiceStatusEngine;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Client;
use Elastica\Index;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StatusController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
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
        $serviceStatusEngine = new ServiceStatusEngine();

        $serviceStatusEngine->add($this->getDatabaseEngineStatus());
        $serviceStatusEngine->add($this->getElasticStatus());
        $serviceStatusEngine->add($this->getFilesystemsPathsStatus());
        $serviceStatusEngine->add($this->getPelagosDatasetCount());
        $serviceStatusEngine->add($this->getPhpStatus());
        $serviceStatusEngine->add($this->getPelagosVersion());

        return new JsonResponse(
            data: $serviceStatusEngine->getStatusArray(),
            status: $serviceStatusEngine->areAllServicesOk() ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Checks the database connection by executing a simple query.
     */
    private function getDatabaseEngineStatus(): ServiceStatus
    {
        $serviceStatus = new ServiceStatus('database');
        try {
            $connection = $this->entityManager->getConnection();
            $result = $connection->executeQuery("SELECT current_setting('server_version_num')");
            $isConnected = $connection->isConnected();
            $fetchedVersion = (int)$result->fetchOne();
            // server_version_num is formed by multiplying the server's major version number by 10000 and adding the minor version number.
            $decodedVersion = (int)round($fetchedVersion / 10000) . '.' . ($fetchedVersion % 10000);
            $serviceStatus->setData(['connection' => $isConnected ? 'Successful' : 'Failed', 'version' => $decodedVersion]);
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
        $serviceStatus = new ServiceStatus('pelagosDatasetCount');
        try {
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $count = $queryBuilder
                ->select('COUNT(dataset.id)')
                ->from(Dataset::class, 'dataset')
                ->getQuery()
                ->getSingleScalarResult();

            $serviceStatus->setData(['numberOfDatasets' => (int) $count]);
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
        $serviceStatus = new ServiceStatus('elasticsearch');
        try {
            $client = $this->elasticaClient;
            $version = $client->getVersion();

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
            $result['version'] = $version;
            $serviceStatus->setData($result);

            if (200 === $indexStatus && ('green' == $status || 'yellow' == $status)) {
                $serviceStatus->setStatus(ServiceStatus::STATUS_OK);
            } else {
                $serviceStatus->setStatus(ServiceStatus::STATUS_ERROR);
            }
        } catch (\Throwable $e) {
            $serviceStatus->setThrowable($e);
        }

        return $serviceStatus;
    }

    /**
     * Test critical filesystem paths.
     */
    private function getFilesystemsPathsStatus(): ServiceStatus
    {
        $serviceStatus = new ServiceStatus('fileSystems');
        $info = [];
        try {
            $uploadDirectory = $this->uploadBaseDir . '/upload';
            $storeDirIsPresent = is_dir($this->storageDir);
            if (!is_dir($this->storageDir)) {
                $serviceStatus->setStatus(ServiceStatus::STATUS_ERROR);
            }
            $info['storageDirIsPresent'] = $storeDirIsPresent;

            $uploadDirIsPresent = is_dir($uploadDirectory);
            if (!is_dir($uploadDirectory)) {
                $serviceStatus->setStatus(ServiceStatus::STATUS_ERROR);
            }
            $info['uploadDirIsPresent'] = $uploadDirIsPresent;

            $uploadDirIsWritable = is_writable($uploadDirectory);
            if (!is_writable($uploadDirectory)) {
                $serviceStatus->setStatus(ServiceStatus::STATUS_ERROR);
            }
            $info['uploadDirIsWritable'] = $uploadDirIsWritable;
        } catch (\Throwable $e) {
            $serviceStatus->setThrowable($e);
        }

        $serviceStatus->setData($info);

        return $serviceStatus;
    }

    private function getPhpStatus(): ServiceStatus
    {
        $serviceStatus = new ServiceStatus('php');
        try {
            $phpVersion = phpversion();
            $serviceStatus->setStatus(ServiceStatus::STATUS_OK);
            $serviceStatus->setData(['version' => $phpVersion]);
        } catch (\Throwable $e) {
            $serviceStatus->setThrowable($e);
        }

        return $serviceStatus;
    }

    private function getPelagosVersion(): ServiceStatus
    {
        $serviceStatus = new ServiceStatus('pelagosVersion');
        $serviceStatus->setData(['version' => $_ENV['PELAGOS_RELEASE_VERSION'] ?? 'unknown']);
        return $serviceStatus;
    }
}

<?php

namespace App\Controller;

use App\Entity\Dataset;
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
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TransformedFinder $searchPelagosFinder,
        private readonly Client $elasticaClient,
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
    public function index(): Response
    {
        $databaseStatus = $this->getDatabaseEngineStatus();
        $elasticsearchStatus = $this->getElasticStatus();
        $pelagosDatasetCount = $this->getPelagosDatasetCount();
        $expectedDatasetCountMin = isset($_ENV['EXPECTED_DATASET_COUNT_MIN']) ? (int) $_ENV['EXPECTED_DATASET_COUNT_MIN'] : 0;

        $overallStatus = $databaseStatus && $elasticsearchStatus && $pelagosDatasetCount >= $expectedDatasetCountMin ? 'ok' : 'error';
        $returnCode = $overallStatus === 'ok' ? 200 : 500;

        $status = [
            'status' => $overallStatus,
            'version' => '1.0.0',
            'timestamp' => (new \DateTime())->format('c'),
            'database' => $databaseStatus,
            'elasticsearch' => $elasticsearchStatus,
            'pelagosDatasetCount' => $pelagosDatasetCount,
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
            $databaseUrl = $_ENV['DATABASE_URL'];
            $parsedUrl = parse_url($databaseUrl);
            $dbUser = $parsedUrl['user'] ?? 'postgres';
            $dbPassword = $parsedUrl['pass'] ?? '';

            $host = $parsedUrl['host'] ?? 'localhost';
            $port = $parsedUrl['port'] ?? 5432;

            if ($parsedUrl === false || !isset($parsedUrl['path'])) {
                return false;
            }

            $databaseName = ltrim($parsedUrl['path'], '/');

            // Try a simple non-data query.
            $connection = new \PDO("pgsql:host=$host;port=$port;dbname=$databaseName", $dbUser, $dbPassword);
            $connection->query('SELECT 1');
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
            $index = new Index($client, $_ENV['SEARCH_TOOL_INDEX']);
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
}

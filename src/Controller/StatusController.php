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

    #[Route('/status', name: 'app_status')]
    public function index(): Response
    {
        $status = [
            'status' => 'ok',
            'version' => '1.0.0',
            'timestamp' => (new \DateTime())->format('c'),
            'database' => $this->getDatabaseStatus(),
            'elasticsearch' => $this->getElasticStatus(),
        ];

        return new JsonResponse(
            data: $status,
        );
    }

    private function getDatabaseStatus(): string
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $query = $queryBuilder
            ->select('dataset')
            ->from(Dataset::class, 'dataset')
            ->getQuery();

        $datasets = new ArrayCollection($query->getResult());
        $status = $this->entityManager->getConnection()->isConnected() ? 'connected' : 'disconnected';

        return $status . ' with ' . $datasets->count() . ' datasets';
    }

    private function getElasticStatus(): string
    {
        $client = new Client();

        // Get the status of a specific index
        $index = new Index($client, 'pelagos_mvde');
        $indexStatus = $index->getStats()->getResponse()->getStatus();

        // Get cluster health
        $clusterHealth = $client->getCluster()->getHealth();
        // Get data from the cluster health object
        $clusterHealthData = $clusterHealth->getData();

        // Accessing specific data within the cluster health data:
        $status = $clusterHealthData['status']; // e.g., green, yellow, red

        return $indexStatus . ' cluster health is ' . $status;
    }
}

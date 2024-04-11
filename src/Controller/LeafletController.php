<?php

namespace App\Controller;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Util\Geometry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LeafletController extends AbstractController
{
    /**
     * The Entity Manager.
     *
     * @var entityManagerInterface
     */
    protected $entityManager;

    /**
     * Class constructor.
     *
     * @param EntityManagerInterface $em A Doctrine entity manager.
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    #[Route('/leaflet/{udi}', name: 'app_leaflet')]
    public function index(string $udi): Response
    {
        return $this->render('leaflet/index.html.twig', [
            'controller_name' => 'LeafletController',
            'udi' => $udi,
        ]);
    }

    #[Route('/leaflet/json/{udi}', name: 'app_leaflet_json', methods: ['GET', 'HEAD'])]
    public function getjson(string $udi): JsonResponse
    {
        $geoUtil = new Geometry($this->entityManager);
        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(array('udi' => $udi));
        if ($dataset instanceof Dataset) {
            $datasetSubmission = $dataset->getDatasetSubmission();
            if ($datasetSubmission instanceof DatasetSubmission) {
                $spatialExtent = $datasetSubmission->getSpatialExtent();
                if ($spatialExtent !== null) {
                    $geoUtil = new Geometry($this->entityManager);
                    $geoJson = $geoUtil->convertGmlToGeoJSON(gml:$spatialExtent, udi:$udi, id:$udi);
                }
            }
        }
        if (isset($geoJson) and $geoJson !== null) {
            $json = new JsonResponse($geoJson, 200, [], true);
        } else {
            $json = new JsonResponse(null, 200, [], false);
        }
        return $json;
    }
}

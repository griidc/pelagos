<?php

namespace App\Controller\UI;

use App\Entity\Dataset;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface as SerializerInterface;

/**
 * GRP metadata export report generator.
 */
class GRPMetadataExportController extends ReportController
{
    /**
     * Var to hold serializer.
     */
    private $serializer;

    /**
     * Var to hold normalizer.
     */
    private $normalizer;

    /**
     * Var to hold encoder, needed for csv creation.
     */
    private $encoder;

    /**
     * Class constructor
     *
     */
    public function __construct(SerializerInterface $serializer, NormalizerInterface $normalizer, EncoderInterface $encoder)
    {
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
        $this->encoder = $encoder;
    }

    /**
     * This is a parameterless report, so all is in the default action.
     *
     * @Route("/grp/export", name="pelagos_app_ui_grpexport_default")
     *
     * @return Response A Response instance.
     *
     */
    public function defaultAction(EntityManagerInterface $entityManager)
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $fileName = 'GRP-Metadata.csv' . (new DateTime('now'))->format('Ymd') . '.csv';

        $udi = 'B1.x126.000:0001';
        $datasetRepository = $entityManager->getRepository(Dataset::class);
        $dataset = $datasetRepository->findBy(['udi' => $udi]);

        $serializedData = $this->serializer->serialize($dataset, 'csv', [
            'groups' => ['export'],
        ]);

        dd($serializedData);
    }
}

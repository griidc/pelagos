<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Doctrine\ORM\Query;

use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DIF;
use Pelagos\Entity\Person;
use Pelagos\Entity\ResearchGroup;

/**
 * The Dataset Monitoring controller.
 *
 * @Route("/stats")
 */
class StatsController extends UIController
{
    /**
     * Statistics Page.
     *
     * @Route("")
     *
     * @return Response
     */
    public function defaultAction()
    {
        // Get the Entity Manager.
        $entityManager = $this
            ->container->get('doctrine.orm.entity_manager');

        // Create a Query Builder for the Dataset Repository.
        $queryBuilder = $entityManager
            ->getRepository(Dataset::class)
            ->createQueryBuilder('dataset');

        // Get the dataset count.
        $datasetCount = $queryBuilder
            ->select($queryBuilder->expr()->count('dataset.id'))
            ->getQuery()->getSingleScalarResult();

        // Recreate a Query Builder for the Person Repository.
        $queryBuilder = $entityManager
            ->getRepository(Person::class)
            ->createQueryBuilder('person');

        // Get the people count.
        $peopleCount = $queryBuilder
            ->select($queryBuilder->expr()->count('person.id'))
            ->getQuery()->getSingleScalarResult();

        // Recreate a Query Builder for the Research Group Repository
        $queryBuilder = $entityManager
            ->getRepository(ResearchGroup::class)
            ->createQueryBuilder('researchGroup');

        // Get the research group count.
        $researchGroupCount = $queryBuilder
            ->select($queryBuilder->expr()->count('researchGroup.id'))
            ->getQuery()->getSingleScalarResult();

        return $this->render(
            'PelagosAppBundle:Stats:index.html.twig',
            $twigData = array(
                'datasets' => $datasetCount,
                'people' => $peopleCount,
                'researchGroups' => $researchGroupCount,
            )
        );
    }

    /**
     * JSON data for Datasets over Time.
     *
     * @Route("/data/total-records-over-time")
     *
     * @return Response
     */
    public function getDatasetOverTimeAction()
    {
        $queryBuilder = $this
            ->container->get('doctrine.orm.entity_manager')
            ->getRepository(DIF::class)
            ->createQueryBuilder('dif');

        $query = $queryBuilder
            ->select($queryBuilder->expr()->count('dif.id'))
            ->select('dif.modificationTimeStamp')
            ->andWhere('dif.status = ' . DIF::STATUS_APPROVED)
            ->orderBy('dif.modificationTimeStamp', 'ASC')
            ->getQuery();

        $identfiedDatasets = $query->getResult(Query::HYDRATE_ARRAY);

        $queryBuilder = $this
            ->container->get('doctrine.orm.entity_manager')
            ->getRepository(Dataset::class)
            ->createQueryBuilder('dataset');

        $query = $queryBuilder
            ->select($queryBuilder->expr()->count('dataset.id'))
            ->select('dataset.modificationTimeStamp')
            ->andWhere('dataset.availabilityStatus = ' . DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE)
            ->orderBy('dataset.modificationTimeStamp', 'ASC')
            ->getQuery();

        $availableDatasets = $query->getResult(Query::HYDRATE_ARRAY);

        $identified = array();
        foreach ($identfiedDatasets as $index => $value) {
            $identified[] = array(($value['modificationTimeStamp']->format('U') * 1000), ($index + 1));
            $index = $index;
        }
        $identified[] = array((time() * 1000), count($identified));

        $available = array();
        foreach ($availableDatasets as $index => $value) {
            $available[] = array(($value['modificationTimeStamp']->format('U') * 1000), ($index + 1));
        }
        $available[] = array((time() * 1000), count($available));

        $result = array();
        $result['page'] = 'overview';
        $result['section'] = 'total-records-over-time';
        $result['data'][0] = array ('label' => 'Identified', 'data' => $identified);
        $result['data'][1] = array ('label' => 'Available', 'data' => $available);

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * JSON data for Dataset Size Ranges.
     *
     * @Route("/data/dataset-size-ranges")
     *
     * @return Response
     */
    public function getDatasetSizeRangesAction()
    {
        $dataSizes = array(
            'KB' => 1000,
            'MB' => pow(1000, 2),
            'GB' => pow(1000, 3),
            'TB' => pow(1000, 4)
        );

        $dataSizeRanges = array(
            array(
                'label' => '< 1 MB',
                'color' => '#c6c8f9',
                'range1' => ($dataSizes['MB'])
            ),
            array(
                'label' => '1 MB - 100 MB',
                'color' => '#88F',
                'range0' => ($dataSizes['MB']),
                'range1' => ($dataSizes['MB'] * 100)
            ),
            array(
                'label' => '100 MB - 1 GB',
                'color' => '#90c593',
                'range0' => ($dataSizes['MB'] * 100),
                'range1' => ($dataSizes['GB'])
            ),
            array(
                'label' => '1 GB - 100 GB',
                'color' => 'yellow',
                'range0' => ($dataSizes['GB']),
                'range1' => ($dataSizes['GB'] * 100)
            ),
            array(
                'label' => '100 GB - 1 TB',
                'color' => '#f6d493',
                'range0' => ($dataSizes['GB'] * 100),
                'range1' => ($dataSizes['TB'])
            ),
            array(
                'label' => '> 1 TB',
                'color' => '#f6b4b5',
                'range0' => ($dataSizes['TB'])
            )
        );

        $repository = $this
            ->container->get('doctrine.orm.entity_manager')
            ->getRepository(DatasetSubmission::class);

        $dataSizes = array();

        foreach ($dataSizeRanges as $index => $range) {
            $queryBuilder = $repository->createQueryBuilder('datasetSubmission');

            $queryBuilder
                ->select($queryBuilder->expr()->count('datasetSubmission.id'));

            if (array_key_exists('range0', $range)) {
                $queryBuilder
                ->andWhere($queryBuilder->expr()->gt('datasetSubmission.datasetFileSize', ':range0'))
                ->setParameter('range0', $range['range0']);
            }

            if (array_key_exists('range1', $range)) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->lt('datasetSubmission.datasetFileSize', ':range1'))
                    ->setParameter('range1', $range['range1']);
            }

            $datasetCount = $queryBuilder->getQuery()->getSingleScalarResult();

            $dataSizes[] = array('label' => $range['label'],
                'data' => array(array($index * 0.971 + 0.171, $datasetCount)),
                'bars' => array('barWidth' => 0.8),
            );
        }

        $datasetSizeRanges = array(
            'page' => 'overview',
            'section' => 'dataset-size-ranges',
            'data' => $dataSizes,
        );

        $response = new Response(json_encode($datasetSizeRanges));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}

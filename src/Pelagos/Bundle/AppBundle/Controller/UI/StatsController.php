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

        // Recreate a Query Builder for the Person Repository.
        $queryBuilder = $entityManager
            ->getRepository(Person::class)
            ->createQueryBuilder('person');

        // Get the people count.
        $peopleCount = $queryBuilder
            ->select($queryBuilder->expr()->count('person.id'))
            ->where(
                $queryBuilder->expr()->gt(
                    'person.id',
                    $queryBuilder->expr()->literal(0)
                )
            )
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
                'datasets' => $entityManager->getRepository(Dataset::class)->countRegistered(),
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
            ->getRepository(DatasetSubmission::class)
            ->createQueryBuilder('datasetSubmission');


        $query = $queryBuilder
            ->select('datasetSubmission.creationTimeStamp')
            ->where('datasetSubmission.id IN (
                        SELECT MIN(subDatasetSubmission.id)
                        FROM ' . DatasetSubmission::class . ' subDatasetSubmission
                        WHERE subDatasetSubmission.datasetFileUri is not null
                        GROUP BY subDatasetSubmission.dataset
                    )')
            ->orderBy('datasetSubmission.creationTimeStamp')
            ->getQuery();

        $registeredDatasets = $query->getResult(Query::HYDRATE_ARRAY);

        $query = $queryBuilder
            ->select('datasetSubmission.creationTimeStamp')
            ->where('datasetSubmission.id IN (
                SELECT MIN(subDatasetSubmission.id)
                FROM ' . DatasetSubmission::class . ' subDatasetSubmission
                WHERE subDatasetSubmission.datasetFileUri is not null
                AND subDatasetSubmission.datasetStatus = :metadatastatus
                AND subDatasetSubmission.restrictions = :restrictedstatus
                AND (
                    subDatasetSubmission.datasetFileTransferStatus = :transerstatuscompleted
                    OR subDatasetSubmission.datasetFileTransferStatus = :transerstatusremotelyhosted
                )
                GROUP BY subDatasetSubmission.dataset
            )')
            ->setParameters(
                array(
                    'metadatastatus' => Dataset::DATASET_STATUS_ACCEPTED,
                    'restrictedstatus' => DatasetSubmission::RESTRICTION_NONE,
                    'transerstatuscompleted' => DatasetSubmission::TRANSFER_STATUS_COMPLETED,
                    'transerstatusremotelyhosted' => DatasetSubmission::TRANSFER_STATUS_REMOTELY_HOSTED,
                )
            )
            ->orderBy('datasetSubmission.creationTimeStamp')
        ->getQuery();


        $availableDatasets = $query->getResult(Query::HYDRATE_ARRAY);

        $registered = array();
        foreach ($registeredDatasets as $index => $value) {
            $registered[] = array(($value['creationTimeStamp']->format('U') * 1000), ($index + 1));
            $index = $index;
        }
        $registered[] = array((time() * 1000), count($registered));

        $available = array();
        foreach ($availableDatasets as $index => $value) {
            $available[] = array(($value['creationTimeStamp']->format('U') * 1000), ($index + 1));
        }
        $available[] = array((time() * 1000), count($available));

        $result = array();
        $result['page'] = 'overview';
        $result['section'] = 'total-records-over-time';
        $result['data'][0] = array ('label' => 'Submitted', 'data' => $registered);
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

        $repository = $this->container->get('doctrine.orm.entity_manager')
            ->getRepository(Dataset::class);

        $dataSizes = array();

        foreach ($dataSizeRanges as $index => $range) {

            $qb = $repository->createQueryBuilder('d');
            $qb->select('count(d.id)');
            $qb->join('d.datasetSubmission', 'ds');

            if (array_key_exists('range0', $range)) {
                $qb->andWhere('ds.datasetFileSize > :range0');
                $qb->setParameter('range0', $range['range0']);
            }
            if (array_key_exists('range1', $range)) {
                $qb->andWhere('ds.datasetFileSize <= :range1');
                $qb->setParameter('range1', $range['range1']);
            }

            $query = $qb->getQuery();
            $datasetCount = $query->getSingleScalarResult();

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

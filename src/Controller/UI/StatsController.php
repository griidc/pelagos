<?php

namespace App\Controller\UI;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

use App\Twig\Extensions as TwigExtentions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DIF;
use App\Entity\Person;
use App\Entity\ResearchGroup;

/**
 * The Dataset Monitoring controller.
 */
class StatsController extends AbstractController
{
    /**
     * The Entity Manager.
     *
     * @var entityManager
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

    /**
     * Statistics Page.
     *
     * @Route("/stats", name="pelagos_app_ui_stats_default")
     *
     * @return Response
     */
    public function defaultAction()
    {
        $this->getStatistics($totalDatasets, $totalSize, $peopleCount, $researchGroupCount);

        return $this->render(
            'Stats/index.html.twig',
            $twigData = array(
                'datasets' => $totalDatasets,
                'totalsize' => $totalSize,
                'people' => $peopleCount,
                'researchGroups' => $researchGroupCount,
            )
        );
    }

    /**
     * Get Statistics Data by reference.
     *
     * @param integer|null $totalDatasets      The total count of datasets.
     * @param string|null  $totalSize          The total size of data.
     * @param integer|null $peopleCount        The total count of people.
     * @param integer|null $researchGroupCount The total count of research groups.
     *
     * @return void
     */
    private function getStatistics(?int &$totalDatasets, ?string &$totalSize, ?int &$peopleCount, ?int &$researchGroupCount) : void
    {
        // Recreate a Query Builder for the Person Repository.
        $queryBuilder = $this->entityManager
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
        $queryBuilder = $this->entityManager
            ->getRepository(ResearchGroup::class)
            ->createQueryBuilder('researchGroup');

        // Get the research group count.
        $researchGroupCount = $queryBuilder
            ->select($queryBuilder->expr()->count('researchGroup.id'))
            ->getQuery()->getSingleScalarResult();

        $datasets = $this->entityManager->getRepository(Dataset::class);

        $totalDatasets = $datasets->countRegistered();
        $totalSize = $datasets->totalDatasetSize();
    }

    /**
     * Get Statistics Data as JSON.
     *
     * @Route("/stats/json", name="pelagos_app_ui_stats_getstatisticsjson")
     *
     * @return Response
     */
    public function getStatisticsJson()
    {
        $this->getStatistics($totalDatasets, $totalSize, $peopleCount, $researchGroupCount);

        $result = array();
        $result['totalDatasets'] = $totalDatasets;
        $result['totalSize'] = TwigExtentions::formatBytes($totalSize, 1);
        $result['peopleCount'] = $peopleCount;
        $result['researchGroupCount'] = $researchGroupCount;

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * JSON data for Datasets over Time.
     *
     * @Route("/stats/data/total-records-over-time", name="pelagos_app_ui_stats_getdatasetovertime")
     *
     * @return Response
     */
    public function getDatasetOverTimeAction()
    {

        $queryBuilder = $this->entityManager
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
     * @Route("/stats/data/dataset-size-ranges", name="pelagos_app_ui_stats_getdatasetsizeranges")
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

        $repository = $this->entityManager
            ->getRepository(Dataset::class);

        $dataSizes = array();

        foreach ($dataSizeRanges as $index => $range) {
            $qb = $repository->createQueryBuilder('d');
            $qb->select('count(d.id)');
            $qb->join('d.datasetSubmission', 'ds');

            if (array_key_exists('range0', $range)) {
                $qb->andWhere('COALESCE(ds.datasetFileColdStorageArchiveSize, ds.datasetFileSize) > :range0');
                $qb->setParameter('range0', $range['range0']);
            }
            if (array_key_exists('range1', $range)) {
                $qb->andWhere('COALESCE(ds.datasetFileColdStorageArchiveSize, ds.datasetFileSize) <= :range1');
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

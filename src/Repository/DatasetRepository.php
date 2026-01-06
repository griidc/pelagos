<?php

namespace App\Repository;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\DOI;
use App\Entity\Funder;
use App\Entity\FundingCycle;
use App\Entity\FundingOrganization;
use App\Entity\ResearchGroup;
use App\Util\FundingOrgFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Dataset Entity Repository class.
 *
 * @extends ServiceEntityRepository<Dataset>
 *
 * @method Dataset|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dataset|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dataset[]    findAll()
 * @method Dataset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatasetRepository extends ServiceEntityRepository
{
    /**
     * Utility to filter by funding organization.
     *
     * @var FundingOrgFilter
     */
    private $fundingOrgFilter;

    /**
     * Constructor.
     *
     * @param ManagerRegistry  $registry         the Registry Manager
     * @param FundingOrgFilter $fundingOrgFilter utility to filter by funding organization
     */
    public function __construct(ManagerRegistry $registry, FundingOrgFilter $fundingOrgFilter)
    {
        parent::__construct($registry, Dataset::class);

        $this->fundingOrgFilter = $fundingOrgFilter;
    }

    /**
     * Count the number of registered Datasets.
     *
     * @param int  $fundingOrganizationId the ID of the FundingOrganization
     * @param bool $accepted              only return accepted datasets
     *
     * @return int
     */
    public function countRegistered(int $fundingOrganizationId = null, bool $accepted = null)
    {
        $qb = $this->createQueryBuilder('dataset')
            ->select('COUNT(dataset)')
            ->where('dataset.datasetSubmissionStatus = :datasetSubmissionStatus')
            ->setParameter('datasetSubmissionStatus', DatasetSubmission::STATUS_COMPLETE);

        if (true === $accepted) {
            $qb
            ->andWhere('dataset.availabilityStatus IN (:available)')
            ->setParameter('available', [
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED,
                DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED,
            ]);
        }

        if (is_numeric($fundingOrganizationId)) {
            $qb
            ->innerJoin('dataset.researchGroup', 'rg')
            ->innerJoin('rg.fundingCycle', 'fc')
            ->andWhere('fc.fundingOrganization = (:foid)')
            ->setParameter('foid', $fundingOrganizationId);
        } elseif ($this->fundingOrgFilter->isActive()) {
            $researchGroupIds = $this->fundingOrgFilter->getResearchGroupsIdArray();

            $qb
            ->innerJoin('dataset.researchGroup', 'rg')
            ->andWhere('rg.id IN (:rgs)')
            ->setParameter('rgs', $researchGroupIds);
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Sum of all dataset file sizes.
     *
     * @param int  $fundingOrganizationId the ID of the FundingOrganization
     * @param bool $accepted              only return accepted datasets
     *
     * @return int size of data in bytes
     */
    public function totalDatasetSize(int $fundingOrganizationId = null, bool $accepted = null): int
    {
        $qb = $this->createQueryBuilder('dataset')
            ->select('SUM(COALESCE(datasetSubmission.coldStorageTotalUnpackedSize, datasetSubmission.datasetFileColdStorageArchiveSize, datasetSubmission.datasetFileSize))')
            ->join('dataset.datasetSubmission', 'datasetSubmission')
            ->where('dataset.datasetSubmissionStatus = :datasetSubmissionStatus')
            ->setParameter('datasetSubmissionStatus', DatasetSubmission::STATUS_COMPLETE);

        if (true === $accepted) {
            $qb
            ->andWhere('dataset.availabilityStatus IN (:available)')
            ->setParameter('available', [
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED,
                DatasetSubmission::AVAILABILITY_STATUS_RESTRICTED_REMOTELY_HOSTED,
            ]);
        }

        if (is_numeric($fundingOrganizationId)) {
            $qb
            ->innerJoin('dataset.researchGroup', 'rg')
            ->innerJoin('rg.fundingCycle', 'fc')
            ->andWhere('fc.fundingOrganization = (:foid)')
            ->setParameter('foid', $fundingOrganizationId);
        } elseif ($this->fundingOrgFilter->isActive()) {
            $researchGroupIds = $this->fundingOrgFilter->getResearchGroupsIdArray();

            $qb
            ->innerJoin('dataset.researchGroup', 'rg')
            ->andWhere('rg.id IN (:rgs)')
            ->setParameter('rgs', $researchGroupIds);
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get datasets with properties matching any values specified by $criteria filtered by text and/or geo filters.
     *
     * @param array  $criteria   an array of criteria
     * @param string $textFilter a string of words to filter by
     * @param string $geoFilter  a WKT string of a geometry to filter by
     * @param int    $hydrator   the hydrator to use
     *
     * @return array
     */
    public function filter(array $criteria, string $textFilter = null, string $geoFilter = null, int $hydrator = Query::HYDRATE_ARRAY)
    {
        $qb = $this->createQueryBuilder('dataset');
        $qb->select('dataset, dif, datasetSubmission, metadata, researchGroup, fundingCycle, fundingOrganization');
        $qb->addSelect('ST_AsText(ST_GeomFromGML(dif.spatialExtentGeometry, 4326)) difSpatialExtentGeometry');
        $qb->join('dataset.dif', 'dif');
        $qb->leftJoin('dataset.datasetSubmission', 'datasetSubmission');
        $qb->leftJoin('dataset.metadata', 'metadata');
        $qb->join('dataset.researchGroup', 'researchGroup');
        $qb->join('researchGroup.fundingCycle', 'fundingCycle');
        $qb->join('fundingCycle.fundingOrganization', 'fundingOrganization');
        foreach ($criteria as $property => $values) {
            $orX = null;
            foreach ($values as $value) {
                if (null === $orX) {
                    $orX = $qb->expr()->orX(
                        $qb->expr()->eq(
                            $property,
                            $qb->expr()->literal($value)
                        )
                    );
                } else {
                    $orX->add(
                        $qb->expr()->eq(
                            $property,
                            $qb->expr()->literal($value)
                        )
                    );
                }
            }
            $qb->andWhere($orX);
        }
        if (null !== $geoFilter) {
            $qb->andWhere(
                'ST_Intersects(
                    ST_GeomFromText(:geometry),
                    CASE
                        WHEN (metadata.id IS NOT NULL) THEN metadata.geometry
                        ELSE ST_GeomFromGML(dif.spatialExtentGeometry)
                    END
                ) = true'
            );
            $qb->setParameter('geometry', "SRID=4326;$geoFilter::geometry");
        }
        if (null !== $textFilter) {
            $searchProperties = [
                'dataset.udi',
                'dif.title',
                'dif.abstract',
                'datasetSubmission.title',
                'datasetSubmission.abstract',
                'datasetSubmission.authors',
                'researchGroup.name',
            ];
            $orX = null;
            $keywords = preg_split('/\s+/', trim($textFilter));
            foreach ($keywords as $index => $keyword) {
                foreach ($searchProperties as $searchProperty) {
                    $like = $qb->expr()->like(
                        $qb->expr()->lower($searchProperty),
                        ':keyword' . $index
                    );
                    if (null === $orX) {
                        $orX = $qb->expr()->orX($like);
                    } else {
                        $orX->add($like);
                    }
                }
                $qb->setParameter(
                    'keyword' . $index,
                    '%' . strtolower($keyword) . '%'
                );
            }
            $qb->andWhere($orX);
        }
        $qb->orderBy('datasetSubmission.creationTimeStamp', 'DESC');
        $qb->addOrderBy('dif.modificationTimeStamp', 'DESC');
        $query = $qb->getQuery();

        return $query->getResult($hydrator);
    }

    /**
     * Creates a new QueryBuilder instance that is prepopulated for this entity and provides a sorted result.
     *
     * @param string $alias   the Entity alias
     * @param string $indexBy the index for the from
     *
     * @return QueryBuilder
     */
    public function createSortedQueryBuilder(string $alias, ?string $indexBy = null)
    {
        $qb = $this->createQueryBuilder($alias)
        ->select($alias);
        // ->from($entityName, $alias, $indexBy);

        if ($this->fundingOrgFilter->isActive()) {
            $researchGroupIds = $this->fundingOrgFilter->getResearchGroupsIdArray();

            $qb
            ->innerJoin($alias . '.researchGroup', 'rg')
            ->andWhere('rg.id IN (:rgs)')
            ->setParameter('rgs', $researchGroupIds);
        }

        $qb->orderBy($alias . '.id');

        return $qb;
    }

    /**
     * Return number of dataset in specified range.
     *
     * @param int|null $lower the lower limit or null
     * @param int|null $upper the upper limit
     *
     * @return int
     */
    public function getDatasetByFileSizeRange(int $lower = null, int $upper = null)
    {
        $qb = $this->createQueryBuilder('dataset');
        $qb->select('count(dataset.id)');
        $qb->join('dataset.datasetSubmission', 'ds');

        if (!empty($lower)) {
            $qb->andWhere('COALESCE(ds.coldStorageTotalUnpackedSize, ds.datasetFileColdStorageArchiveSize, ds.datasetFileSize) > :lower');
            $qb->setParameter('lower', $lower);
        }
        if (!empty($upper)) {
            $qb->andWhere('COALESCE(ds.coldStorageTotalUnpackedSize, ds.datasetFileColdStorageArchiveSize, ds.datasetFileSize) <= :upper');
            $qb->setParameter('upper', $upper);
        }

        if ($this->fundingOrgFilter->isActive()) {
            $researchGroupIds = $this->fundingOrgFilter->getResearchGroupsIdArray();

            $qb
            ->innerJoin('dataset.researchGroup', 'rg')
            ->andWhere('rg.id IN (:rgs)')
            ->setParameter('rgs', $researchGroupIds);
        }

        $query = $qb->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * Get datasets based on associated funders.
     *
     * @return array an array of Datasets
     */
    public function findByFunder(Funder $funder): array
    {
        $qb = $this->createQueryBuilder('dataset');
        $qb->setParameter('funder', $funder);
        $qb->where($qb->expr()->isMemberOf(':funder', 'dataset.funders'));

        return $qb->getQuery()->getResult();
    }

    public function getListOfApprovedDatasetWithoutKeywords(): array
    {
        $qb = $this->createQueryBuilder('d');
        $qb
            ->select('d.udi, d.title, d.acceptedDate')
            ->addSelect($qb->expr()->count('k.id') . ' as keywords')
            ->join(DatasetSubmission::class, 'ds', 'WITH', 'd.datasetSubmission = ds.id')
            ->leftJoin('ds.keywords', 'k')
            ->where('d.datasetStatus = ?1')
            ->groupBy('d.udi, d.acceptedDate, d.title')
            ->orderBy('d.acceptedDate', 'DESC')
            ->setParameter(1, Dataset::DATASET_STATUS_ACCEPTED)
        ;

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get a list of datasets that have a DOI.
     *
     * @return Dataset[]
     */
    public function getDatasetWithDoiSet(): array
    {
        $queryBuilder = $this->createQueryBuilder('d');

        return
            $queryBuilder
            ->where($queryBuilder->expr()->isNotNull('d.doi'))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Return datasets by research group for dataset monitoring.
     *
     * @param string|null $researchGroup The string ID for the research group
     */
    public function getDatasetsBy(string $researchGroup = null, string $fundingCycle = null, string $fundingOrganization = null): array
    {
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder
        ->select('d.udi as UDI, doi.doi as DOI, d.title as Title, d.datasetStatus as Status, rg.name as Research_Group, fc.name as Funding_Cycle, fo.name as Funding_Organization')
        ->join(DOI::class, 'doi', 'WITH', 'd.doi = doi.id')
        ->join(ResearchGroup::class, 'rg', 'WITH', 'd.researchGroup = rg.id')
        ->join(FundingCycle::class, 'fc', 'WITH', 'rg.fundingCycle = fc.id')
        ->join(FundingOrganization::class, 'fo', 'WITH', 'fc.fundingOrganization = fo.id')
        ;

        if (!empty($fundingCycle)) {
            $queryBuilder
            ->where('fc.id = ?1')
            ->setParameter(1, $fundingCycle);
        }

        if (!empty($researchGroup)) {
            $queryBuilder
            ->where('rg.id = ?1')
            ->setParameter(1, $researchGroup);
        }

        if (!empty($fundingOrganization)) {
            $queryBuilder
            ->where('fo.id = ?1')
            ->setParameter(1, $fundingOrganization);
        }

        return
            $queryBuilder
            ->orderBy('d.udi', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}

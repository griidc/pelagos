<?php

namespace Pelagos\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * Dataset Entity Repository class.
 */
class DatasetRepository extends EntityRepository
{
    /**
     * Count the number of registered Datasets.
     *
     * @return integer
     */
    public function countRegistered()
    {
        return $this->createQueryBuilder('dataset')
            ->select('COUNT(dataset)')
            ->where('dataset.datasetSubmissionStatus = :datasetSubmissionStatus')
            ->setParameter('datasetSubmissionStatus', DatasetSubmission::STATUS_COMPLETE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Sum of all dataset file sizes.
     *
     * @return integer Size of data in bytes.
     */
    public function totalDatasetSize() : int
    {
        return $this->createQueryBuilder('dataset')
            ->select('SUM(COALESCE(datasetSubmission.datasetFileColdStorageArchiveSize,datasetSubmission.datasetFileSize))')
            ->join('dataset.datasetSubmission', 'datasetSubmission')
            ->where('dataset.datasetSubmissionStatus = :datasetSubmissionStatus')
            ->setParameter('datasetSubmissionStatus', DatasetSubmission::STATUS_COMPLETE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get datasets with properties matching any values specified by $criteria filtered by text and/or geo filters.
     *
     * @param array   $criteria   An array of criteria.
     * @param string  $textFilter A string of words to filter by.
     * @param string  $geoFilter  A WKT string of a geometry to filter by.
     * @param integer $hydrator   The hydrator to use.
     *
     * @return array
     */
    public function filter(array $criteria, $textFilter = null, $geoFilter = null, $hydrator = Query::HYDRATE_ARRAY)
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
            $searchProperties = array(
                'dataset.udi',
                'dif.title',
                'dif.abstract',
                'datasetSubmission.title',
                'datasetSubmission.abstract',
                'datasetSubmission.authors',
                'researchGroup.name',
            );
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
     * @param string $alias   The Entity alias.
     * @param string $indexBy The index for the from.
     *
     * @return QueryBuilder
     */
    public function createSortedQueryBuilder($alias, $indexBy = null)
    {
        return $this->_em->createQueryBuilder()
            ->select($alias)
            ->from($this->_entityName, $alias, $indexBy)
            ->orderBy($alias . '.id');
    }
}

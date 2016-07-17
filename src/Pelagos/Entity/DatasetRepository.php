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
                $qb->expr()->orX(
                    'ST_Intersects(ST_GeomFromText(:geometry), metadata.geometry) = true',
                    'ST_Intersects(ST_GeomFromText(:geometry), ST_GeomFromGML(dif.spatialExtentGeometry)) = true'
                )
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
            $keywords = preg_split('/\s+/', $textFilter);
            foreach ($keywords as $index => $keyword) {
                foreach ($searchProperties as $searchProperty) {
                    $like = $qb->expr()->like(
                        $qb->expr()->lower($searchProperty),
                        ':keyword' . $index . str_replace('.', '_', $searchProperty)
                    );
                    if (null === $orX) {
                        $orX = $qb->expr()->orX($like);
                    } else {
                        $orX->add($like);
                    }
                    $qb->setParameter(
                        'keyword' . $index . str_replace('.', '_', $searchProperty),
                        '%' . strtolower($keyword) . '%'
                    );
                }
            }
            $qb->andWhere($orX);
        }
        $query = $qb->getQuery();
        return $query->getResult($hydrator);
    }
}

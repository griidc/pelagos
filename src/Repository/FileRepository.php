<?php

namespace App\Repository;

use App\Entity\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * File Entity Repository class.
 *
 * @method File|null find($id, $lockMode = null, $lockVersion = null)
 * @method File|null findOneBy(array $criteria, array $orderBy = null)
 * @method File[]    findAll()
 * @method File[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileRepository extends ServiceEntityRepository
{
    /**
     * FileRepository constructor.
     *
     * @param ManagerRegistry $registry Register class instance.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    /**
     * Query for getting filename and physical filepath.
     *
     * @param array $fileIds List of fileIds to be queried on.
     *
     * @return array
     */
    public function getFilePathNameAndPhysicalPath(array $fileIds) : array
    {
        $queryBuilder = $this->createQueryBuilder('file');

        $queryBuilder
            ->select('file.filePathName, file.physicalFilePath')
            ->where('file.id IN (:fileIds)')
            ->setParameter('fileIds', $fileIds);

        return $queryBuilder->getQuery()->getResult(Query::HYDRATE_ARRAY);
    }
}

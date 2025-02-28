<?php

namespace App\Controller\Api;

use App\Entity\File;
use App\Entity\Fileset;
use App\Entity\Dataset;
use App\Entity\DIF;
use App\Entity\DistributionPoint;
use App\Entity\PersonDatasetSubmissionDatasetContact;
use App\Entity\PersonDatasetSubmissionMetadataContact;
use App\Event\EntityEventDispatcher;
use App\Event\LogActionItemEventDispatcher;
use App\Form\DatasetType;
use App\Message\DeleteFile;
use App\Message\DeleteDir;
use App\Repository\DatasetRepository;
use App\Util\Datastore;
use App\Util\MdappLogger;
use App\Util\ZipFiles;
use FOS\RestBundle\Controller\Annotations\View;
use GuzzleHttp\Psr7\Utils as GuzzlePsr7Utils;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * The Dataset api controller.
 */
class DatasetController extends EntityController
{
    /**
     * Get a count of Datasets.
     *
     * @param Request $request The request object.
     *
     *
     *
     *
     * @return integer
     */
    #[View]
    #[Route(path: '/api/datasets/count', name: 'pelagos_api_datasets_count', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function countAction(Request $request)
    {
        return $this->handleCount(Dataset::class, $request);
    }

    /**
     * Get a collection of Datasets.
     *
     * @param Request $request The request object.
     *
     *
     *
     *
     * @return array
     */
    #[View(serializerEnableMaxDepthChecks: true)]
    #[Route(path: '/api/datasets', name: 'pelagos_api_datasets_get_collection', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(Dataset::class, $request);
    }

    /**
     * Get a single Dataset for a given id.
     *
     * @param integer $id The id of the Dataset to return.
     *
     *
     *
     *
     * @return Dataset
     */
    #[View(serializerEnableMaxDepthChecks: true)]
    #[Route(path: '/api/datasets/{id}', name: 'pelagos_api_datasets_get', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function getAction(int $id)
    {
        return $this->handleGetOne(Dataset::class, $id);
    }

    /**
     * Suggest a citation for a Dataset identified by UDI.
     *
     * @param integer $id The ID of the Dataset to suggest a citation for.
     *
     *
     *
     *
     * @return string
     */
    #[View]
    #[Route(path: '/api/datasets/{id}/citation', name: 'pelagos_api_datasets_get_citation', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function getCitationAction(int $id)
    {
        $dataset = $this->handleGetOne(Dataset::class, $id);
        return $dataset->getCitation();
    }

    /**
     * Update a Dataset with the submitted data.
     *
     * @param integer     $id          The id of the Dataset to update.
     * @param Request     $request     The request object.
     * @param MdappLogger $mdappLogger The mdapp logger utility.
     *
     *
     *
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    #[Route(path: '/api/datasets/{id}', name: 'pelagos_api_datasets_patch', methods: ['PATCH'], defaults: ['_format' => 'json'])]
    public function patchAction(int $id, Request $request, MdappLogger $mdappLogger)
    {
        $this->handleUpdate(DatasetType::class, Dataset::class, $id, $request, 'PATCH');
        $jiraLinkValue = $request->request->get('issueTrackingTicket');
        if (null !== $jiraLinkValue) {
            $mdappLogger->writeLog(
                $this->getUser()->getUserIdentifier() .
                ' set Jira Link for udi: ' .
                $this->entityHandler->get(Dataset::class, $id)->getUdi() .
                ' to ' .
                $jiraLinkValue .
                '.' .
                ' (api msg)'
            );
        }
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Dataset and associated Metadata and Difs.
     *
     * @param integer                      $id                            The id of the Dataset to delete.
     * @param EntityEventDispatcher        $entityEventDispatcher         The entity event dispatcher.
     * @param MessageBusInterface          $messageBus                    Symfony messenger message bus interface.
     * @param LogActionItemEventDispatcher $logActionItemEventDistpatcher The Log action item event dispatcher.
     *
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    #[Route(path: '/api/datasets/{id}', name: 'pelagos_api_datasets_delete', methods: ['DELETE'], defaults: ['_format' => 'json'])]
    public function deleteAction(int $id, EntityEventDispatcher $entityEventDispatcher, MessageBusInterface $messageBus, LogActionItemEventDispatcher $logActionItemEventDispatcher)
    {
        /** @var Dataset $dataset */
        $dataset = $this->handleGetOne(Dataset::class, $id);

        $dif = $dataset->getDif();

        $datasetSubmissionHistory = $dataset->getDatasetSubmissionHistory();

        foreach ($datasetSubmissionHistory as $datasetSub) {
            $datasetContacts = $datasetSub->getDatasetContacts();
            foreach ($datasetContacts as $datasetContact) {
                $datasetContactId = $datasetContact->getId();
                $this->handleDelete(PersonDatasetSubmissionDatasetContact::class, $datasetContactId);
            }
            $metadataContacts = $datasetSub->getMetadataContacts();
            foreach ($metadataContacts as $metadataContact) {
                $metadataContactId = $metadataContact->getId();
                $this->handleDelete(PersonDatasetSubmissionMetadataContact::class, $metadataContactId);
            }
            $distributionPoints = $datasetSub->getDistributionPoints();
            foreach ($distributionPoints as $distributionPoint) {
                $distributionPointId = $distributionPoint->getId();
                $this->handleDelete(DistributionPoint::class, $distributionPointId);
            }
            $fileset = $datasetSub->getFileset();

            if ($fileset instanceof Fileset) {
                $this->deleteFilesOnDisk($fileset, $messageBus);
            }
        }

        $entityEventDispatcher->dispatch($dataset, 'delete_doi');

        $udi = $dataset->getUdi();

        $logActionItemEventDispatcher->dispatch(
            array(
                'actionName' => 'Dataset Deletion',
                'subjectEntityName' => 'Pelagos\Entity\Dataset',
                'subjectEntityId' => $dataset->getId(),
                'payLoad' => array('UDI' => $udi, 'userId' => $this->getUser()->getUserIdentifier()),
            ),
            'dataset_deletion'
        );

        $this->handleDelete(Dataset::class, $id);

        if ($dif instanceof DIF) {
            $this->handleDelete(DIF::class, $dif->getId());
        }

        return $this->makeNoContentResponse();
    }

    /**
     * Method to delete files on disk.
     *
     * @param Fileset             $fileset    Fileset which contains all the files that need to be deleted.
     * @param MessageBusInterface $messageBus Symfony messenger message bus interface.
     *
     * @return void
     */
    private function deleteFilesOnDisk(Fileset $fileset, MessageBusInterface $messageBus): void
    {
        if (!$fileset->isDone()) {
            foreach ($fileset->getAllFiles() as $file) {
                $fileStatus = $file->getStatus();
                // Deleting files from the uploads directory
                if (in_array($fileStatus, [File::FILE_NEW, File::FILE_ERROR])) {
                    $filePath = $file->getPhysicalFilePath();
                    @unlink($filePath);
                    @rmdir(dirname($filePath));
                }
            }
        }
        // Delete all the folders/files for the given dataset
        $deleteDirPath = $fileset->getFileRootPath();
        $deleteDirMessage = new DeleteDir($fileset->getDatasetSubmission()->getDataset()->getUdi(), $deleteDirPath);
        $messageBus->dispatch($deleteDirMessage);

        if ($fileset->doesZipFileExist()) {
            $deleteFileMessage = new DeleteFile($fileset->getZipFilePath(), false);
            $messageBus->dispatch($deleteFileMessage);
        }
    }

    /**
     * File number of files and total size for all datasets by UDI.
     *
     * @param DatasetRepository $datasetRepository The Dataset Repository.
     *
     *
     * @return Response
     */
    #[View]
    #[Route(path: '/api/datasetFileCountSize/{udi}', name: 'pelagos_api_datasets_file_count_size', methods: ['GET'], defaults: ['_format' => 'json'])]
    public function getFileCountSize(DatasetRepository $datasetRepository, ?string $udi): Response
    {

        if ($udi) {
            $dataset = $datasetRepository->findOneBy(['udi' => $udi]);
            if (null === $dataset) {
                return new JsonResponse(['error' => 'Dataset not found'], Response::HTTP_NOT_FOUND);
            }
            $datasets = [$dataset];
        } else {
            $datasets = $datasetRepository->findAll(); // no UDI is provided, get all datasets
        }


        $data = [];

        foreach ($datasets as $dataset) {
            $datasetArray = array(
                "udi" => $dataset->getUdi(),
                "numberOfFiles" => $dataset->getNumberOfFiles(),
                "totalFileSize" => $dataset->getTotalFileSize(),
                "isColdStored" => $dataset->isColdStored(),
                "coldSize" => $dataset->getDatasetSubmission()?->getColdStorageTotalUnpackedSize(),
                "coldFileCount" => $dataset->getDatasetSubmission()?->getColdStorageTotalUnpackedCount(),
                "datasetLifeCycleStatus" => $dataset->getDatasetLifecycleStatus(),
            );
            $data[] = $datasetArray;
        }

        return new JsonResponse($data);
    }

    /**
     * Ouput zip file.
     *
     *
     * @param Dataset $dataset
     * @return Response
     */
    #[Route(path: '/api/datasets/zip/{dataset}', name: 'pelagos_api_download_zip')]
    public function getZipStream(Dataset $dataset, ZipFiles $zipFiles, Datastore $datastore): Response
    {
        $zipFileName = str_replace(':', '.', $dataset->getUdi()) . '.zip';

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $zipFileName,
        );

        $headers = array(
            'Content-Disposition' => $disposition,
            'Content-type' => 'application/zip',
        );

        return new StreamedResponse(function () use ($dataset, $zipFiles, $datastore, $zipFileName) {
            $outputStream = GuzzlePsr7Utils::streamFor(fopen('php://output', 'wb'));
            $zipFiles->start($outputStream, $zipFileName);

            $fileset = $dataset->getDatasetSubmission()->getFileset();

            foreach ($fileset->getProcessedFiles() as $file) {
                $filePathName = $file->getFilePathName();
                $fileStream = $datastore->getFile($file->getPhysicalFilePath());
                $zipFiles->addFile($filePathName, $fileStream);
            }

            $zipFiles->finish();
        }, 200, $headers);
    }
}

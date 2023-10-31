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
use App\Form\DatasetType;
use App\Message\DeleteFile;
use App\Message\DeleteDir;
use App\Repository\DatasetRepository;
use App\Util\Datastore;
use App\Util\MdappLogger;
use App\Util\ZipFiles;
use FOS\RestBundle\Controller\Annotations\View;
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
     * @Route("/api/datasets/count", name="pelagos_api_datasets_count", methods={"GET"}, defaults={"_format"="json"})
     *
     * @View()
     *
     * @return integer
     */
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
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @Route("/api/datasets", name="pelagos_api_datasets_get_collection", methods={"GET"}, defaults={"_format"="json"})
     *
     * @return array
     */
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
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @Route("/api/datasets/{id}", name="pelagos_api_datasets_get", methods={"GET"}, defaults={"_format"="json"})
     *
     * @return Dataset
     */
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
     * @Route("/api/datasets/{id}/citation", name="pelagos_api_datasets_get_citation", methods={"GET"}, defaults={"_format"="json"})
     *
     * @View()
     *
     * @return string
     */
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
     * @Route("/api/datasets/{id}", name="pelagos_api_datasets_patch", methods={"PATCH"}, defaults={"_format"="json"})
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction(int $id, Request $request, MdappLogger $mdappLogger)
    {
        $this->handleUpdate(DatasetType::class, Dataset::class, $id, $request, 'PATCH');
        $jiraLinkValue = $request->request->get('issueTrackingTicket');
        if (null !== $jiraLinkValue) {
            $mdappLogger->writeLog(
                $this->getUser()->getUserName() .
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
     * @param integer               $id                    The id of the Dataset to delete.
     * @param EntityEventDispatcher $entityEventDispatcher The entity event dispatcher.
     * @param MessageBusInterface   $messageBus            Symfony messenger message bus interface.
     *
     *
     *
     * @Route("/api/datasets/{id}", name="pelagos_api_datasets_delete", methods={"DELETE"}, defaults={"_format"="json"})
     *
     * @return Response A response object with an empty body and a "no content" status code.
     */
    public function deleteAction(int $id, EntityEventDispatcher $entityEventDispatcher, MessageBusInterface $messageBus)
    {
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
     * @Route("/api/datasetFileCountSize", name="pelagos_api_datasets_file_count_size", methods={"GET"}, defaults={"_format"="json"})
     *
     * @View()
     *
     * @return Response
     */
    public function getFileCountSize(DatasetRepository $datasetRepository): Response
    {
        $datasets = $datasetRepository->findAll();

        $data = [];

        foreach ($datasets as $dataset) {
            $datasetArray = array (
                "udi" => $dataset->getUdi(),
                "numberOfFiles" => $dataset->getNumberOfFiles(),
                "totalFileSize" => $dataset->getTotalFileSize(),
            );
            $data[] = $datasetArray;
        }

        return new JsonResponse($data);
    }

    /**
     * Ouput zip file.
     *
     * @Route("/api/datasets/zip/{dataset}")
     *
     * @param Dataset $dataset
     * @return Response
     */
    public function getZipSteam(Dataset $dataset, ZipFiles $zipFiles, Datastore $datastore): Response
    {
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'zipfile.zip',
        );

        $headers = array(
            'Content-Disposition' => $disposition,
            'Content-type' => 'application/zip',
        );

        return new StreamedResponse(function () use ($dataset, $zipFiles, $datastore) {
            $fileStream = fopen('php://output', 'wb');
            $outputStream = array('fileStream' => $fileStream);
            $zipFiles->start($outputStream, 'zipout.zip');

            $fileset = $dataset->getDatasetSubmission()->getFileset();

            $filesInfo = array();
            foreach ($fileset->getProcessedFiles() as $file) {
                $filesInfo[$file->getId()]['filePathName'] = $file->getFilePathName();
                $filesInfo[$file->getId()]['physicalFilePath'] = $file->getPhysicalFilePath();
            }

            foreach ($filesInfo as $fileItemInfo) {
                for ($i = 1; $i <= 100000; ++$i) {
                    $zipFiles->addFile($fileItemInfo['filePathName'] . $i, $datastore->getFile($fileItemInfo['physicalFilePath']));
                }
            }

            $zipFiles->finish();

            // return $fileStream;
        }, 200, $headers);
    }
}

<?php

namespace App\Event;

use App\Entity\File;
use App\Entity\DatasetSubmission;
use App\Message\ZipDatasetFiles;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

class FileProcessListener
{
    /**
     * The Message Bus.
     *
     * @var MessageBusInterface
     */
    protected $messageBus;

    /**
     * Constructor.
     *
     * @param MessageBusInterface $messageBus The Message Bus.
     */
    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * Pre update method for file entity, to update file status.
     *
     * @param File               $file An instance of File entity.
     * @param LifecycleEventArgs $args Doctrine life cycle event args.
     *
     * @return void
     */
    public function preUpdate(File $file, LifecycleEventArgs $args): void
    {
        $file = $args->getObject();
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $metadata = $entityManager->getClassMetadata(get_class($file));
        if ($file instanceof File) {
            if (!empty($file->getFileSha256Hash())
                and ($file->getStatus() === File::FILE_IN_PROGRESS)) {
                $file->setStatus(File::FILE_DONE);
                $unitOfWork->recomputeSingleEntityChangeSet($metadata, $file);
            }
        }
    }

    /**
     * Post update method for file entity, to send zipper message when fileset is done.
     *
     * @param File               $file An instance of File entity.
     * @param LifecycleEventArgs $args Doctrine life cycle event args.
     *
     * @return void
     */
    
    public function postUpdate(File $file, LifecycleEventArgs $args): void
    {
        if (!$file instanceof File) {
            return;
        }

        $fileset = $file->getFileset();
        $datasetSubmission = $fileset->getDatasetSubmission();

        if (!$datasetSubmission instanceof DatasetSubmission) {
            return;
        }

        if ($fileset->isDone()) {
            $datasetSubmissionId = $datasetSubmission->getId();
            $fileIds = array();
            foreach ($fileset->getProcessedFiles() as $file) {
                $fileIds[] = $file->getId();
            }
            // Dispatch message to zip files
            $zipFiles = new ZipDatasetFiles($fileIds, $datasetSubmissionId);
            $this->messageBus->dispatch($zipFiles);
        }
    }
}

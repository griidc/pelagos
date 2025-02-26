<?php

namespace App\MessageHandler;

use App\Message\DeleteFile;
use App\Util\Datastore;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class DeleteFileHandler
{
    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param LoggerInterface $logger    name hinted delete_file logger
     * @param Datastore       $datastore datastore utility instance
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Datastore $datastore,
    ) {
    }

    /**
     * Invoke function to mark file as deleted.
     *
     * @param DeleteFile $deleteFile the DeleteFile message to be handled
     */
    public function __invoke(DeleteFile $deleteFile)
    {
        try {
            $filePath = $deleteFile->getFilePath();
            if ($deleteFile->isInStore()) {
                $this->datastore->deleteFile($filePath);
            } else {
                @unlink($filePath);
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Unable to delete file. Message: "%s"', $e->getMessage()));
        }
    }
}

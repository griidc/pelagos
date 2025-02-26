<?php

namespace App\MessageHandler;

use App\Message\DeleteDir;
use App\Util\Datastore;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class DeleteDirHandler
{
    /**
     * Constructor for this Controller, to set up default services.
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly Datastore $datastore,
    ) {
    }

    /**
     * Invoke function to delete folder and its contents.
     *
     * @param DeleteDir $deleteDir the DeleteDir message to be handled
     */
    public function __invoke(DeleteDir $deleteDir)
    {
        $udi = $deleteDir->getDatasetUdi();
        $folderPath = $deleteDir->getDeleteDirPath();

        $this->logger->info(sprintf('Delete directory worked started for UDI: "%s"', $udi));

        try {
            $this->datastore->deleteDir($folderPath);
            $this->logger->info(sprintf('Delete all folder/files successful for UDI: "%s"', $udi));
        } catch (\Exception $e) {
            $this->logger->error(sprintf(sprintf('Unable to delete folder. Message: "%s"', $e->getMessage())));
        }
        $this->logger->info(sprintf('Delete directory worker for UDI: "%s" completed ', $udi));
    }
}

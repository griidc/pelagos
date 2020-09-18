<?php

namespace App\MessageHandler;

use App\Message\DeleteFile;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeleteFileHandler implements MessageHandlerInterface
{

    public function __invoke(DeleteFile $deleteFile)
    {
        $fileId = $deleteFile->getFileId();
        $this->logger->info(sprintf('Processing File with ID: %d', $fileId));
    }
}

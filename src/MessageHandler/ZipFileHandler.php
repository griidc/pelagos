<?php

namespace App\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

use App\Message\ZipFile;

/**
 * Handler for zipping files for download.
 */
class ZipFileHandler implements MessageHandlerInterface
{

    /**
     * ZipFileHandler constructor.
     */
    public function __construct()
    {
    }

    public function __invoke(ZipFile $zipFile)
    {
        // TODO: Implement __invoke() method.

    }
}
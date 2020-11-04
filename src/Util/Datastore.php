<?php

namespace App\Util;

use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;

use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Datastore utility class which manipulates files on disk.
 */
class Datastore
{
    /**
     * Flysystem interface object for Datastore.
     *
     * @var FilesystemInterface
     */
    private $datastoreFlysystem;

    /**
     * Relative path for files folder.
     */
    const FILES_DIRECTORY = 'files';

    /**
     * Logger interface instance for Monolog default channel.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Datastore constructor.
     *
     * @param FilesystemInterface $datastoreFlysystem Datastore flystystem instance.
     * @param LoggerInterface     $logger             Monolog logger interface instance.
     */
    public function __construct(FilesystemInterface $datastoreFlysystem, LoggerInterface $logger)
    {
        $this->datastoreFlysystem = $datastoreFlysystem;
        $this->logger = $logger;
    }
    
    /**
     * Retrieves a file from disk.
     *
     * @param string $filePath The retrieve file path.
     *
     * @throws \Exception Exception thrown when read stream fails.
     *
     * @return array
     */
    public function getFile(string $filePath): array
    {
        $resource['fileStream'] = $this->datastoreFlysystem->readStream($filePath);

        if ($resource['fileStream'] === false) {
            throw new \Exception(sprintf('Error opening stream for "%s"', $filePath));
        }

        return $resource;
    }

    /**
     * Moves an uploaded file to datastore disk location.
     *
     * @param array $fileStream File stream resource object.
     *
     * @return string
     */
    public function addFile(array $fileStream): string
    {
        $uuid = Uuid::uuid4();
        $destinationPath = self::FILES_DIRECTORY . DIRECTORY_SEPARATOR . $uuid->toString();
        try {
            $this->datastoreFlysystem->writeStream($destinationPath, $fileStream['fileStream']);
        } catch (FileExistsException $e) {
            $this->logger->error(sprintf('File already exists. Message: "%s"', $e->getMessage()));
        }

        if (is_resource($fileStream['fileStream'])) {
            fclose($fileStream['fileStream']);
        }
        return $destinationPath;
    }

    /**
     * Deletes a file from the disk.
     *
     * @param string $filePath File path for the file that is to be removed.
     *
     * @return bool
     */
    public function deleteFile(string $filePath): bool
    {
        return $this->datastoreFlysystem->delete($filePath);
    }
}

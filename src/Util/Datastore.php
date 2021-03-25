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
     * Logger interface instance for Monolog default channel.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * String appended to filename to mark as deleted.
     */
    const MARK_FILE_AS_DELETED = '_DELETED';

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
     * @param array  $fileStream   File stream resource object.
     * @param string $filePathName File destination path on datastore.
     *
     * @return string
     */
    public function addFile(array $fileStream, string $filePathName): string
    {
        $newFilePathName = $this->makeFileName($filePathName);
        $newFilePathName = FileUtilities::fixFileNameLength($newFilePathName);
        try {
            $this->datastoreFlysystem->writeStream($newFilePathName, $fileStream['fileStream']);
        } catch (FileExistsException $e) {
            $this->logger->error(sprintf('File already exists. Message: "%s"', $e->getMessage()));
        }

        if (is_resource($fileStream['fileStream'])) {
            fclose($fileStream['fileStream']);
        }
        return $newFilePathName;
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

    /**
     * Renames a file on the disk.
     *
     * @param string $oldFilePath Old file path that needs to be renamed.
     * @param string $newFilePath New file path for the file.
     * @param bool   $deleteFlag  Delete flag for rename.
     *
     * @return string
     */
    public function renameFile(string $oldFilePath, string $newFilePath, bool $deleteFlag = false): string
    {
        if ($deleteFlag === false) {
            $newFilePath = $this->makeFileName($newFilePath);
        }
        $newFilePath = FileUtilities::fixFileNameLength($newFilePath);
        $this->datastoreFlysystem->rename($oldFilePath, $newFilePath);
        return $newFilePath;
    }

    /**
     * Makes a unique filename.
     *
     * @param string $fileName Filename that needs to be made unique.
     *
     * @return string
     */
    private function makeFileName(string $fileName) : string
    {
        $uuid = Uuid::uuid4()->toString();
        // add only last 5 bytes of uuid to the destination path
        $fileName .= '_' . substr($uuid, -5);

        return $fileName;
    }

    /**
     * Deletes a folder from the disk.
     *
     * @param string $dirPath File path for the folder that is to be removed.
     *
     * @return bool
     */
    public function deleteDir(string $dirPath): bool
    {
        if ($this->datastoreFlysystem->has($dirPath)) {
            return $this->datastoreFlysystem->deleteDir($dirPath);
        }
        return false;
    }
}

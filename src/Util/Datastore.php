<?php

namespace App\Util;

use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

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
     * Checks whether a file exists or not.
     *
     * @param string $filePath The file path.
     *
     * @return bool
     */
    public function has(string $filePath): bool
    {
        return $this->datastoreFlysystem->has($filePath);
    }

    /**
     * Queries Flysys for a file's mimetype
     *
     * @param string $filePath The file's path.
     *
     * @return string|false
     */
    public function getMimeType(string $filePath)
    {
        return $this->datastoreFlysystem->getMimetype($filePath);
    }

    /**
     * Moves an uploaded file to datastore disk location.
     *
     * @param string $filePathName File destination path on datastore.
     *
     * @return string
     */
    public function addFile(StreamInterface $stream, string $filePathName): string
    {
        $newFilePathName = FileNameUtilities::makeFileName($filePathName);
        $newFilePathName = FileNameUtilities::fixFileNameLength($newFilePathName);
        $resource = $stream->detach();

        try {
            $this->datastoreFlysystem->writeStream($newFilePathName, $resource);
        } catch (FileExistsException $e) {
            $this->logger->error(sprintf('File already exists. Message: "%s"', $e->getMessage()));
        }

        if (is_resource($resource)) {
            fclose($resource);
        }
        return $newFilePathName;
    }

    /**
     * Deletes a file from the disk.
     *
     * @param string  $filePath  File path for the file that is to be removed.
     * @param boolean $deleteDir If the path is a directory, and should be deleted.
     *
     * @throws \Exception Exception thrown when file delete is failed.
     *
     * @return bool
     */
    public function deleteFile(string $filePath, bool $deleteDir = false): bool
    {
        if ($deleteDir) {
             $deleteFile = $this->deleteDir($filePath);
        } else {
            $deleteFile = $this->datastoreFlysystem->delete($filePath);
        }
        $path = dirname($filePath);
        $deleteDir = true;
        $contents = $this->datastoreFlysystem->listContents($path, true);

        $contents = array_filter($contents, function ($array) {
            if (array_key_exists('type', $array) and $array['type'] === 'file') {
                return $array;
            }
        });

        if (empty($contents)) {
            $deleteDir = $this->deleteFile($path, true);
        }

        return $deleteFile & $deleteDir;
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
            $newFilePath = FileNameUtilities::makeFileName($newFilePath);
        }
        $newFilePath = FileNameUtilities::fixFileNameLength($newFilePath);
        $this->datastoreFlysystem->rename($oldFilePath, $newFilePath);
        return $newFilePath;
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

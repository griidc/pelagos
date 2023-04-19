<?php

namespace App\Util;

use GuzzleHttp\Psr7\Stream;
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
     * Logger interface instance for Monolog default channel.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * String appended to filename to mark as deleted.
     */
    public const MARK_FILE_AS_DELETED = '_DELETED';

    /**
     * Datastore constructor.
     *
     * @param LoggerInterface $logger monolog logger interface instance
     */
    public function __construct(private FilesystemInterface $datastoreFlysystem, LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Retrieves a file from disk.
     *
     * @param string $filePath the retrieve file path
     *
     * @throws \Exception exception thrown when read stream fails
     */
    public function getFile(string $filePath): Stream
    {
        $resource = $this->datastoreFlysystem->readStream($filePath);

        if (false === $resource) {
            throw new \Exception(sprintf('Error opening stream for "%s"', $filePath));
        }

        return new Stream($resource);
    }

    /**
     * Checks whether a file exists or not.
     *
     * @param string $filePath the file path
     */
    public function has(string $filePath): bool
    {
        return $this->datastoreFlysystem->has($filePath);
    }

    /**
     * Queries Flysys for a file's mimetype.
     *
     * @param string $filePath the file's path
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
     * @param StreamInterface $fileStream   file stream of the file to add
     * @param string          $filePathName file destination path on datastore
     */
    public function addFile(StreamInterface $fileStream, string $filePathName): string
    {
        $newFilePathName = FileNameUtilities::makeFileName($filePathName);
        $newFilePathName = FileNameUtilities::fixFileNameLength($newFilePathName);
        $fileResource = $fileStream->detach();
        try {
            if (is_resource($fileResource)) {
                $this->datastoreFlysystem->writeStream($newFilePathName, $fileResource);
                fclose($fileResource);
            }
        } catch (FileExistsException $e) {
            $this->logger->error(sprintf('File already exists. Message: "%s"', $e->getMessage()));
        }

        return $newFilePathName;
    }

    /**
     * Deletes a file from the disk.
     *
     * @param string $filePath  file path for the file that is to be removed
     * @param bool   $deleteDir if the path is a directory, and should be deleted
     *
     * @throws \Exception exception thrown when file delete is failed
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
            if (array_key_exists('type', $array) and 'file' === $array['type']) {
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
     * @param string $oldFilePath old file path that needs to be renamed
     * @param string $newFilePath new file path for the file
     * @param bool   $deleteFlag  delete flag for rename
     */
    public function renameFile(string $oldFilePath, string $newFilePath, bool $deleteFlag = false): string
    {
        if (false === $deleteFlag) {
            $newFilePath = FileNameUtilities::makeFileName($newFilePath);
        }
        $newFilePath = FileNameUtilities::fixFileNameLength($newFilePath);
        $this->datastoreFlysystem->rename($oldFilePath, $newFilePath);

        return $newFilePath;
    }

    /**
     * Deletes a folder from the disk.
     *
     * @param string $dirPath file path for the folder that is to be removed
     */
    public function deleteDir(string $dirPath): bool
    {
        if ($this->datastoreFlysystem->has($dirPath)) {
            return $this->datastoreFlysystem->deleteDir($dirPath);
        }

        return false;
    }
}

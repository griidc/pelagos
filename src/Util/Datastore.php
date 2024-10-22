<?php

namespace App\Util;

use GuzzleHttp\Psr7\Utils as GuzzlePsr7Utils;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToWriteFile;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

/**
 * Datastore utility class which manipulates files on disk.
 */
class Datastore
{
    /**
     * String appended to filename to mark as deleted.
     */
    public const MARK_FILE_AS_DELETED = '_DELETED';

    /**
     * Configuration options
     */
    public array $config = [
        'visibility' => 'public',
        'directory_visibility' => 'public'
    ];

    /**
     * Datastore constructor.
     */
    public function __construct(private FilesystemOperator $datastoreFlysystem, private LoggerInterface $logger)
    {
    }

    /**
     * Retrieves a file from disk.
     *
     * @param string $filePath the retrieve file path
     */
    public function getFile(string $filePath): StreamInterface
    {
        $resource = $this->datastoreFlysystem->readStream($filePath);
        $stream = GuzzlePsr7Utils::streamFor($resource);

        return $stream;
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
     */
    public function getMimeType(string $filePath): string
    {
        return $this->datastoreFlysystem->mimeType($filePath);
    }

    /**
     * Moves an uploaded file to datastore disk location.
     *
     * @param string $filePathName file destination path on datastore
     */
    public function addFile(StreamInterface $stream, string $filePathName): string
    {
        $newFilePathName = FileNameUtilities::makeFileName($filePathName);
        $newFilePathName = FileNameUtilities::fixFileNameLength($newFilePathName);
        $resource = $stream->detach();

        try {
            $this->datastoreFlysystem->writeStream($newFilePathName, $resource, $this->config);
        } catch (FilesystemException | UnableToWriteFile $exception) {
            $this->logger->error(sprintf('Unable to write. Message: "%s"', $exception->getMessage()));
        }

        if (is_resource($resource)) {
            fclose($resource);
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

        $contents = $this->datastoreFlysystem->listContents($path, true)
        ->filter(fn (StorageAttributes $attributes) => $attributes->isFile())
        ->map(fn (StorageAttributes $attributes) => $attributes->path())
        ->toArray();

        if (empty($contents)) {
            $deleteDir = $this->deleteFile($path, true);
        }

        return $deleteFile && $deleteDir;
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
        if ($deleteFlag === false) {
            $newFilePath = FileNameUtilities::makeFileName($newFilePath);
        }
        $newFilePath = FileNameUtilities::fixFileNameLength($newFilePath);
        try {
            $this->datastoreFlysystem->move($oldFilePath, $newFilePath, $this->config);
        } catch (FilesystemException | UnableToMoveFile $exception) {
            $this->logger->error(sprintf('Unable to rename file. Message: "%s"', $exception->getMessage()));
        }

        return $newFilePath;
    }

    /**
     * Deletes a folder from the disk.
     *
     * @param string $dirPath file path for the folder that is to be removed
     */
    public function deleteDir(string $dirPath): bool
    {
        $success = false;
        if ($this->datastoreFlysystem->has($dirPath)) {
            $this->datastoreFlysystem->deleteDirectory($dirPath);
            $success = true;
        }

        return $success;
    }
}

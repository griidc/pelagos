<?php

namespace App\Util;

use League\Flysystem\FileExistsException;
use League\Flysystem\FilesystemInterface;
use Ramsey\Uuid\Uuid;

class Datastore
{
    /**
     * @var FilesystemInterface
     */
    private $datastoreFlysystem;

    /**
     * Relative path for files folder.
     */
    const FILES_DIRECTORY = 'files';

    /**
     * Datastore constructor.
     *
     * @param FilesystemInterface $datastoreFlysystem
     */
    public function __construct(FilesystemInterface $datastoreFlysystem)
    {
        $this->datastoreFlysystem = $datastoreFlysystem;
    }

    /**
     * Retrieves a file from disk.
     *
     * @param string $filePath
     *
     * @throws \Exception
     *
     * @return resource
     */
    public function getFile(string $filePath): resource
    {
        $resource = $this->datastoreFlysystem->readStream($filePath);

        if ($resource === false) {
            throw new \Exception(sprintf('Error opening stream for "%s"', $filePath));
        }

        return $resource;
    }

    /**
     * Moves an uploaded file to datastore disk location.
     *
     * @param resource $fileStream
     *
     * @return string
     */
    public function addFile(resource $fileStream): string
    {
        $uuid = Uuid::uuid4();
        $destinationPath = self::FILES_DIRECTORY . DIRECTORY_SEPARATOR . $uuid->toString();
        try {
            $this->datastoreFlysystem->writeStream($destinationPath, $fileStream);
        } catch (FileExistsException $e) {
            // TODO implement logger
        }

        if (is_resource($fileStream)) {
            fclose($fileStream);
        }
        return $destinationPath;
    }

    /**
     * Deletes a file from the disk.
     *
     * @param string $filePath
     *
     * @return void
     */
    public function deleteFile(string $filePath): void
    {
        $this->datastoreFlysystem->delete($filePath);
    }
}

<?php

namespace App\Util;

use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;

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
    public function getFile(string $filePath)
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
     * @param string $filePath
     *
     * @return string
     */
    public function copyFile(string $filePath): string
    {
        $destinationPath = self::FILES_DIRECTORY . DIRECTORY_SEPARATOR . basename($filePath);
        try {
            $this->datastoreFlysystem->copy($filePath, $destinationPath);
        } catch (FileExistsException $e) {
            // TODO implement logger
        } catch (FileNotFoundException $e) {
            // TODO implement logger
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

    /**
     * Retrieves all files in a dataset.
     */
    public function getAllFiles()
    {
        // TODO implement method

    }
}

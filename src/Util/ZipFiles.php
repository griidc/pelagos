<?php

namespace App\Util;

use Doctrine\Common\Collections\Collection;

use League\Flysystem\FilesystemInterface;

use ZipStream\Option\Archive;
use ZipStream\ZipStream;

use App\Entity\File;

class ZipFiles
{
    /**
     * Flysystem interface object for Datastore.
     *
     * @var FilesystemInterface
     */
    private $datastoreFlysystem;

    /**
     * ZipFiles constructor.
     *
     * @param FilesystemInterface $datastoreFlysystem Datastore flystystem instance.
     */
    public function __construct(FilesystemInterface $datastoreFlysystem)
    {
        $this->datastoreFlysystem = $datastoreFlysystem;
    }

    /**
     * Creates a zip file from collection of files.
     *
     * @param Collection|File $files   Files that need to be zipped.
     * @param string          $zipFile Filename of the zipfile.
     *
     * @throws \Exception When utility class can not open/write to zip file.
     *
     * @return void
     */
    public function createZipFile(Collection $files, string $zipFile) : void
    {
        $zip = new ZipStream($zipFile);
        foreach ($files as $file) {
            $zip->addFileFromStream($file->getFileName(), $this->datastoreFlysystem->readStream($file->getFilePath));
        }
        $zip->finish();
    }
}

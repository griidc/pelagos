<?php

namespace App\Util;

use League\Flysystem\FilesystemInterface;

use ZipStream\ZipStream;

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
     * @param Datastore $datastoreFlysystem Datastore flystystem instance.
     */
    public function __construct(Datastore $datastoreFlysystem)
    {
        $this->datastoreFlysystem = $datastoreFlysystem;
    }

    /**
     * Creates a zip file from collection of files.
     *
     * @param array  $fileInfo Files that need to be zipped.
     * @param string $zipFile  Filename of the zipfile.
     *
     * @throws \Exception When utility class can not open/write to zip file.
     *
     * @return void
     */
    public function createZipFile(array $fileInfo, string $zipFile) : void
    {
        $zip = new ZipStream($zipFile);
        foreach ($fileInfo as $file) {
            $fileStream = $this->datastoreFlysystem->getFile($file['filePath']);
            if ($fileStream and is_resource($fileStream['fileStream'])) {
                $zip->addFileFromStream($file['fileName'], $fileStream['fileStream']);
            }
        }
        $zip->finish();
    }
}

<?php

namespace App\Util;

use League\Flysystem\FilesystemInterface;

use ZipStream\ZipStream;

class ZipFiles
{
    /**
     * Datastore utility instance.
     *
     * @var Datastore
     */
    private $datastore;

    /**
     * ZipFiles constructor.
     *
     * @param Datastore $datastore Datastore utility instance.
     */
    public function __construct(Datastore $datastore)
    {
        $this->datastore = $datastore;
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
            $fileStream = $this->datastore->getFile($file['filePath']);
            if ($fileStream and is_resource($fileStream['fileStream'])) {
                $zip->addFileFromStream($file['fileName'], $fileStream['fileStream']);
            }
        }
        $zip->finish();
    }
}

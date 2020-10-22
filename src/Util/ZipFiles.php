<?php

namespace App\Util;

use App\Entity\File;

use Doctrine\Common\Collections\Collection;

use ZipArchive;

class ZipFiles
{

    /**
     * ZipArchive class instance.
     *
     * @var ZipArchive
     */
    private $zip;

    /**
     * ZipFiles constructor.
     */
    public function __construct()
    {
        $this->zip = new ZipArchive();
    }

    /**
     * Creates a zip file from collection of files.
     *
     * @param Collection|File $files   Files that need to be zipped.
     * @param string          $zipFile Filename of the zipfile.
     *
     * @throws \Exception When utility class can not open/write to zip file.
     *
     * @return string
     */
    public function createZipFile(Collection $files, string $zipFile) : string
    {
        $flag = (file_exists($zipFile))? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE;
        if ($this->zip->open($zipFile, $flag) === true) {
            foreach ($files as $file) {
                $this->zip->addFile($file->getFilePath(), $file->getFileName());
            }
            $this->zip->close();
        } else {
            throw new \Exception('Unable to create zip file');
        }

        return $zipFile;
    }
}

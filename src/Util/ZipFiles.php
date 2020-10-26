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
     * The filename of the temporary zipfile.
     *
     * @var zipFile
     */
    private $zipFile;

    /**
     * Temporary file location for zip processing.
     *
     * @var tempFileLocation
     */
    protected $tempFileLocation = '/tmp/';

    /**
     * ZipFiles constructor.
     */
    public function __construct()
    {
        $this->zip = new ZipArchive();
    }

    /**
     * ZipFiles destructor.
     */
    public function __destruct() {
        @unlink($this->zipFile);
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

    /**
     * Create a new zip file to add files to as stream.
     *
     * @return void
     */
    public function start() : void
    {
        $this->zipFile = tempnam($this->tempFileLocation, 'PELAGOS_ZIP');

        $flag = (file_exists($this->zipFile))? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE;
        if ($this->zip->open($this->zipFile, $flag) !== true) {
            throw new \Exception('Unable to create zip file');
        }
    }

    /**
     * Add File to zip.
     *
     * @return boolean Return true if success.
     */
    public function addFile(string $fileName, array $fileStream) : bool
    {
        $stream = $fileStream['fileStream'];
        $tempFile = tempnam($this->tempFileLocation, 'PELAGOS_TMP');
        $targetFile = fopen($tempFile, 'wb');
        stream_copy_to_stream($stream, $targetFile);
        fclose($stream);
        fclose($targetFile);
        $result = $this->zip->addFile($tempFile, $fileName);

        // Close the zip so it will save. Then open it again (in append mode).
        $this->zip->close();
        $this->zip->open($this->zipFile);
        unlink($tempFile);

        return $result;
    }

    /**
     * Done with adding file to the zip, and return file stream.
     *
     * @return array Return the file stream.
     */
    public function finish() : array
    {
        $this->zip->close();
        $fileStream['fileStream'] = fopen($this->zipFile, 'rb');
        return $fileStream;
    }
}

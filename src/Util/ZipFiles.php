<?php

namespace App\Util;

use ZipStream\Option\Archive;
use ZipStream\ZipStream;

class ZipFiles
{
    /**
     * @var ZipStream
     */
    private $zip;

    /**
     * Start the file zip.
     *
     * @param array  $outputFileStream Zip output file stream.
     * @param string $zipFileName      Zip file name.
     *
     * @return void
     */
    public function start(array $outputFileStream, string $zipFileName): void
    {
        $options = new Archive();
        $options->setOutputStream($outputFileStream['fileStream']);
        $this->zip = new ZipStream($zipFileName, $options);
    }

    /**
     * Add file to the zip.
     *
     * @param string $fileName   File name of the file that needs to be zipped.
     * @param array  $fileStream File stream of the file that needs to be zipped.
     *
     * @return void
     */
    public function addFile(string $fileName, array $fileStream): void
    {
        if (!empty($fileStream) and is_resource($fileStream['fileStream'])) {
            $fileName = FileUtilities::fixFileName($fileName);
            $this->zip->addFileFromStream($fileName, $fileStream['fileStream']);
        }
    }

    /**
     * Finish zipping the file.
     *
     * @return void
     */
    public function finish(): void
    {
        $this->zip->finish();
    }
}

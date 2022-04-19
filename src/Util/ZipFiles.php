<?php

namespace App\Util;

use Psr\Http\Message\StreamInterface;
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
    public function start(StreamInterface $outputFileStream, string $zipFileName): void
    {
        $options = new Archive();
        $options->setOutputStream($outputFileStream);
        // $options->setDeflateLevel(9);
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
    public function addFile(string $fileName, StreamInterface $fileStream): void
    {
        if ($fileStream->isReadable()) {
            $options = new \ZipStream\Option\File();
            // Turned off deflate
            // $options->setMethod(\ZipStream\Option\Method::STORE());
            $this->zip->addFileFromPsr7Stream($fileName, $fileStream, $options);
            // $this->zip->addFileFromStream($fileName, $fileStream->detach());
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

<?php

namespace App\Util;

use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Component\HttpFoundation\Request;

class FileUploader
{
    /**
     * The directory where uploaded files will be placed.
     *
     * @var string
     */
    private $uploadDirectory;

    /**
     * The directory where file chunks will be stored.
     *
     * @var string
     */
    private $chunksDirectory;

    public function __construct(string $homedirPrefix)
    {
        $this->uploadDirectory = $homedirPrefix . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'files';
        $this->chunksDirectory = $homedirPrefix . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'chunks';
    }

    /**
     * File uploader service.
     *
     * @param Request $request
     *
     * @return void
     */
    public function upload(Request $request): void
    {
        $totalChunks = $request->get('chunkCount');
        $chunkIndex = $request->get('chunkIndex');
        $uploadedFile = $request->files->get('file');
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

        if ($totalChunks > 1) {
            $chunksFolder = $this->chunksDirectory . DIRECTORY_SEPARATOR . $originalFilename;
            $this->isFolder($chunksFolder);
            if (!file_exists($chunksFolder)) {
                mkdir($chunksFolder, 0755, true);
            }
            $uploadedFile->move(
                $chunksFolder,
                $chunkIndex
            );

            if ((int)$totalChunks === ($chunkIndex + 1)) {
                //combine chunks
                $targetDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $this->generateGuid();
                $this->isFolder($targetDirectory);
                $targetFile = fopen($targetDirectory . DIRECTORY_SEPARATOR . $newFilename, 'wb');

                for ($i = 0; $i < $totalChunks; $i++) {
                    $chunk = fopen(
                        $chunksFolder .
                        DIRECTORY_SEPARATOR .
                        $i,
                        'rb'
                    );
                    stream_copy_to_stream($chunk, $targetFile);
                    fclose($chunk);
                    unlink($chunksFolder . DIRECTORY_SEPARATOR . $i);
                }
                // Success
                fclose($targetFile);
                rmdir($chunksFolder);
            }
        } else {
            $targetDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $this->generateGuid();
            $this->isFolder($targetDirectory);
            $uploadedFile->move(
                $targetDirectory,
                $newFilename
            );
        }
    }

    /**
     * Generate random GUID.
     *
     * @return string
     */
    private function generateGuid() : string
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }

    /**
     * Checks if folder exists.
     *
     * @param string $targetFolder
     *
     * @return void
     */
    private function isFolder(string $targetFolder): void
    {
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0755, true);
        }
    }
}

<?php

namespace App\Util;

use Gedmo\Sluggable\Util\Urlizer;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @param Request $request Symfony request instance.
     *
     * @return array
     */
    public function upload(Request $request): array
    {
        dump($request);
        $totalChunks = $request->get('dztotalchunkcount');
        $chunkIndex = $request->get('dzchunkindex');
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $fileExtension = $uploadedFile->guessExtension();
        $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$fileExtension;
        $uuid = $request->get('dzuuid');
        $targetDirectory = '';
        $fileSize = $uploadedFile->getSize();

        if ($totalChunks > 1) {
            $chunksFolder = $this->chunksDirectory . DIRECTORY_SEPARATOR . $uuid;
            $this->isFolder($chunksFolder);
            $isChunk = true;
            $uploadedFile->move(
                $chunksFolder,
                $chunkIndex
            );

            if ((int)$totalChunks === ($chunkIndex + 1)) {
                //combine chunks
                $isChunk = false;
                $targetDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $uuid;
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
            $fileMetadata = array(
                'chunk' => $isChunk,
                'path' => $targetDirectory . DIRECTORY_SEPARATOR . $newFilename,
                'name' => $originalFilename . '.' . $fileExtension,
                'size' => $fileSize,
            );
        } else {
            $targetDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $uuid;
            $this->isFolder($targetDirectory);
            $uploadedFile->move(
                $targetDirectory,
                $newFilename
            );

            $fileMetadata = array(
                'chunk' => false,
                'path' => $targetDirectory . DIRECTORY_SEPARATOR . $newFilename,
                'name' => $originalFilename . '.' . $fileExtension,
                'size' => $fileSize,
            );
        }

        return $fileMetadata;
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

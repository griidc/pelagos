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
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $fileExtension = $uploadedFile->guessExtension();
        $fileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME) .'.'. $fileExtension;
        $uuid = $request->get('dzuuid');
        $totalChunks = $request->get('dztotalchunkcount');
        $targetDirectory = '';
        $fileSize = $uploadedFile->getSize();
        $chunksIndex = $request->get('dzchunkindex');
        if ($totalChunks > 1) {
            $chunksFolder = $this->chunksDirectory . DIRECTORY_SEPARATOR . $uuid;
            $this->isFolder($chunksFolder);
            $isChunk = true;
            $uploadedFile->move(
                $chunksFolder,
                $chunksIndex
            );

            $fileMetadata = array(
                'chunk' => $isChunk,
                'path' => $targetDirectory . DIRECTORY_SEPARATOR . $fileName,
                'name' => $uploadedFile->getClientOriginalName() . '.' . $fileExtension,
                'size' => (int)$fileSize,
            );
        } else {
            $targetDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $uuid;
            $this->isFolder($targetDirectory);
            $uploadedFile->move(
                $targetDirectory,
                $fileName
            );

            $fileMetadata = array(
                'chunk' => false,
                'path' => $targetDirectory . DIRECTORY_SEPARATOR . $fileName,
                'name' => $uploadedFile->getClientOriginalName() . '.' . $fileExtension,
                'size' => (int)$fileSize,
            );
        }

        return $fileMetadata;
    }

    /**
     * Checks if folder exists.
     *
     * @param string $targetFolder Target Folder path.
     *
     * @return void
     */
    private function isFolder(string $targetFolder): void
    {
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0755, true);
        }
    }

    /**
     * To combine all the uploaded chunks into a single file.
     *
     * @param Request $request Symfony request instance.
     *
     * @return array
     */
    public function combineChunks(Request $request): array
    {
        $uuid = $request->get('dzuuid');
        $fileName = $request->get('fileName');
        $totalChunks = $request->get('dztotalchunkcount');
        $fileSize = $request->get('dztotalfilesize');

        $chunksFolder = $this->chunksDirectory . DIRECTORY_SEPARATOR . $uuid;
        //combine chunks
        $isChunk = false;
        $targetDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $uuid;
        $this->isFolder($targetDirectory);
        $targetFile = fopen($targetDirectory . DIRECTORY_SEPARATOR . $fileName, 'wb');
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

        return array(
            'chunk' => $isChunk,
            'path' => $targetDirectory . DIRECTORY_SEPARATOR . $fileName,
            'name' => $fileName,
            'size' => (int)$fileSize,
        );
    }
}

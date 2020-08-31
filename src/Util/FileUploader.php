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
        $fileInfo = $this->getFileInfo($request);
        $newFilename = Urlizer::urlize($fileInfo['originalFilename']).'-'.uniqid().'.'.$fileInfo['fileExtension'];

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');

        $targetDirectory = '';
        $fileSize = $uploadedFile->getSize();

        if ($fileInfo['totalChunks'] > 1) {
            $chunksFolder = $this->chunksDirectory . DIRECTORY_SEPARATOR . $fileInfo['uuid'];
            $this->isFolder($chunksFolder);
            $isChunk = true;
            $uploadedFile->move(
                $chunksFolder,
                $fileInfo['chunksIndex']
            );

            $fileMetadata = array(
                'chunk' => $isChunk,
                'path' => $targetDirectory . DIRECTORY_SEPARATOR . $newFilename,
                'name' => $fileInfo['originalFilename'] . '.' . $fileInfo['fileExtension'],
                'size' => $fileSize,
            );
        } else {
            $targetDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $fileInfo['fileExtension'];
            $this->isFolder($targetDirectory);
            $uploadedFile->move(
                $targetDirectory,
                $newFilename
            );

            $fileMetadata = array(
                'chunk' => false,
                'path' => $targetDirectory . DIRECTORY_SEPARATOR . $newFilename,
                'name' => $fileInfo['originalFilename'] . '.' . $fileInfo['uuid'],
                'size' => $fileSize,
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
        $fileInfo = $this->getFileInfo($request);
        $newFilename = Urlizer::urlize($fileInfo['originalFilename']).'-'.uniqid().'.'.$fileInfo['fileExtension'];

        $chunksFolder = $this->chunksDirectory . DIRECTORY_SEPARATOR . $fileInfo['uuid'];
        //combine chunks
        $isChunk = false;
        $targetDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $fileInfo['uuid'];
        $this->isFolder($targetDirectory);
        $targetFile = fopen($targetDirectory . DIRECTORY_SEPARATOR . $newFilename, 'wb');
        dump($fileInfo);
        for ($i = 0; $i < $fileInfo['totalChunks']; $i++) {
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
            'path' => $targetDirectory . DIRECTORY_SEPARATOR . $newFilename,
            'name' => $fileInfo['originalFilename'] . '.' . $fileInfo['fileExtension'],
            'size' => $fileInfo['fileSize'],
        );
    }

    /**
     * Retrieves file information from the Uploaded File object.
     *
     * @param Request $request Symfony request instance.
     *
     * @return array
     */
    private function getFileInfo(Request $request): array
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        dump($uploadedFile);
        return array(
            'uuid' => $request->get('dzuuid'),
            'totalChunks' => $request->get('dztotalchunkcount'),
            'originalFilename' => pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME),
            'fileExtension' => $uploadedFile->guessExtension(),
            'chunksIndex' => $request->get('dzchunkindex'),
            'fileSize' => $uploadedFile->getSize(),
            );
    }
}

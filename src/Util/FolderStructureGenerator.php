<?php

namespace App\Util;

use App\Repository\FilesetRepository;

class FolderStructureGenerator
{

    /**
     * An instance of a Fileset Repository.
     *
     * @var FilesetRepository
     */
    protected $filesetRepository;

    /**
     * Class constructor for FolderStructureGenerator.
     *
     * @param FilesetRepository $filesetRepository Fileset Repository instance.
     */
    public function __construct(FilesetRepository $filesetRepository)
    {
        $this->filesetRepository = $filesetRepository;
    }

    /**
     * Get folder and file structure in json for File Manager.
     *
     * @param integer $filesetId Fileset id for the files.
     * @param string $path
     *
     * @return array
     */
    public function getFolderJson(int $filesetId, string $path = '') : array
    {
        $fileset = $this->filesetRepository->find($filesetId);
        $fileData = array();
        $reducedFilePaths = array();
        foreach ($fileset->getFilesInDirectory($path) as $file) {
            $reducedFilePaths[] = str_replace($path, '', $file->getFilePathName());
        }
        dump($reducedFilePaths);
        exit;

        return $fileData;
    }

}

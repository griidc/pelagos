<?php

namespace App\Util;

use App\Repository\FilesetRepository;
use SplMinHeap;

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
            dump($file);
            $individualDirectoriesArray = explode('/', str_replace($path, '', $file->getFilePathName()));
            $reducedFilePaths[] = array_shift($individualDirectoriesArray);
        }
        dump($reducedFilePaths);
        exit;
        $h = new SplMinHeap();

// [parent, child]
        $h->insert([9, 11]);
        $h->insert([0, 1]);
        $h->insert([1, 2]);
        $h->insert([1, 3]);
        $h->insert([1, 4]);
        $h->insert([1, 5]);
        $h->insert([3, 6]);
        $h->insert([2, 7]);
        $h->insert([3, 8]);
        $h->insert([5, 9]);
        $h->insert([9, 10]);

        for ($h->top(); $h->valid(); $h->next()) {
            list($parentId, $myId) = $h->current();
            dump("$myId ($parentId)\n");
        }
        exit;
        return array();
    }

}

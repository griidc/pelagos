<?php

namespace App\Util;

use App\Repository\FilesetRepository;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
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
        $individualDirectoriesArray = array();
        $folderStructureHeap = new SplMinHeap();
        $folderStructure = array(
            'items' => array()
        );
//        foreach ($fileset->getFilesInDirectory($path) as $file) {
//            $individualDirectoriesArray[] = explode('/', str_replace($path, '', $file->getFilePathName()));
//            $filePaths[$file->getId()] = $file->getFilePathName();
//        }

        $filePaths = array(
            0 => 'test/hi/hello/a.rtf',
            1 => 'test/hi/s.rtf',
            2 => 'test/hi/hello/c.rtf',
            3 => 'test/f.rtf',
            4 => 'test/how/a.txt'
        );
        $newTree = $this->explodeTree($filePaths, "/");

        //create a recursive iterator to loop over the array recursively
        $iterator = new RecursiveIteratorIterator(
            new RecursiveArrayIterator($newTree),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $iterator->beginIteration();

        $this->repeatTree($iterator);

        exit;
//        dump($iterator->callGetChildren());
//        exit;
        foreach ($iterator as $key => $value) {
            dump($iterator->callGetChildren());
            exit;
//            $jsonFolder = array(
//                'name' => $key,
//                'isDirectory' => $iterator->callHasChildren(),
//                'items' => $iterator->callGetChildren()
//            );
        }
        exit;
        $individualDirectoriesArray = array(
            0 => array(
                0 => 'test',
                1 => 'hello',
                2 => 'hi',
                3 => 'a.rtf'
            ),
            1 => array(
                0 => 'test',
                1 => 'hello',
                2 => 's.rtf'
            )
        );

        // [parent, child]
//        $h->insert([9, 11]);
//        $h->insert([0, 1]);
//        $h->insert([1, 2]);
//        $h->insert([1, 3]);
//        $h->insert([1, 4]);
//        $h->insert([1, 5]);
//        $h->insert([3, 6]);
//        $h->insert([2, 7]);
//        $h->insert([3, 8]);
//        $h->insert([5, 9]);
//        $h->insert([9, 10]);
//        foreach ($individualDirectoriesArray as $individualDirectory) {
//            for ($i = 0; $i < count($individualDirectory) - 1 ; $i++) {
//                $folderStructureHeap->insert([$individualDirectory[$i], $individualDirectory[$i + 1]]);
//            }
//        }
//
//
//        for ($folderStructureHeap->top(); $folderStructureHeap->valid(); $folderStructureHeap->next()) {
//            dump($folderStructureHeap->key());
//            list($parentId, $myId) = $folderStructureHeap->current();
//            dump("$myId ($parentId)");
//        }

        return array();
    }


    private function explodeTree(array $filePaths, $delimiter = '_')
    {
        $splitRE   = '/' . preg_quote($delimiter, '/') . '/';
        $tree = array();
        foreach ($filePaths as $fileId => $filePath) {
            // Get parent parts and the current leaf
            $parts	= preg_split($splitRE, $filePath, -1, PREG_SPLIT_NO_EMPTY);
            $leafPart = array_pop($parts);

            $parentArr = $tree;
            foreach ($parts as $index => $part) {
                if (!isset($parentArr[$part])) {
                    $parentArr[$part] = array();
                }
                $parentArr = $parentArr[$part];
            }

            // Add the final part to the structure
            if (empty($parentArr[$leafPart])) {
                $parentArr[$leafPart] = $fileId;
            }
        }
        return $tree;
    }

    private function repeatTree($iterator)
    {
        dump($iterator->current());
        dump($iterator->key());
        $iterator->next();
        if ($iterator->callHasChildren()) {
            $this->repeatTree($iterator);
        } else {
            $iterator->endIteration();
        }
    }
}

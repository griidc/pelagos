<?php

namespace Pelagos\Util;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * A class for manipulating the data store.
 */
class DataStore
{

    /**
     * The data store directory.
     *
     * @var string
     */
    protected $dataStoreDirectory;

    /**
     * The data download directory.
     *
     * @var string
     */
    protected $dataDownloadDirectory;

    /**
     * Constructor.
     *
     * @param string $dataStoreDirectory    The data store directory.
     * @param string $dataDownloadDirectory The data download directory.
     */
    public function __construct($dataStoreDirectory, $dataDownloadDirectory)
    {
        $this->dataStoreDirectory = $dataStoreDirectory;
        $this->dataDownloadDirectory = $dataDownloadDirectory;
    }

    /**
     * Add a file to the data store.
     *
     * @param string $fileUri   The URI of the file to add.
     * @param string $datasetId The id of the dataset to add the file to.
     * @param string $type      The type (dataset or metadata) of the file.
     *
     * @throws \Exception When the file URI is not set.
     * @throws \Exception When the file does not exist.
     *
     * @return void
     */
    public function addFile($fileUri, $datasetId, $type)
    {
        if (null === $fileUri) {
            throw new \Exception("$type file URI not set");
        }
        if (preg_match('#^(file://|/)#', $fileUri) and !file_exists($fileUri)) {
            throw new \Exception("File: $fileUri not found!");
        }
        $storeFileName = $this->getStoreFileName($datasetId, $type);
        $storeFilePath = $this->addFileToDataStoreDirectory($fileUri, $datasetId, $storeFileName);
        $this->createLinkInDownloadDirectory($storeFilePath, $datasetId, $storeFileName);
    }

    /**
     * Get the info for a file in the data store.
     *
     * @param string $datasetId The id of the dataset to add the file to.
     * @param string $type      The type (dataset or metadata) of the file.
     *
     * @return File
     */
    public function getFileInfo($datasetId, $type)
    {
        $dataStoreDirectory = $this->getDataStoreDirectory($datasetId);
        $storeFileName = $this->getStoreFileName($datasetId, $type);
        return new File("$dataStoreDirectory/$storeFileName");
    }

    /**
     * Get the info for the linked download file for a file in the data store.
     *
     * @param string $datasetId The id of the dataset to add the file to.
     * @param string $type      The type (dataset or metadata) of the file.
     *
     * @return File
     */
    public function getDownloadFileInfo($datasetId, $type)
    {
        $dataDownloadDirectory = $this->getDataDownloadDirectory($datasetId);
        $storeFileName = $this->getStoreFileName($datasetId, $type);
        return new File("$dataDownloadDirectory/$storeFileName");
    }

    /**
     * Add a file to the data store directory.
     *
     * @param string $fileUri       The URI of the file to add.
     * @param string $datasetId     The id of the dataset.
     * @param string $storeFileName The name of the file in the data store.
     *
     * @throws \Exception When unable to delete an existing file in the data store.
     * @throws \Exception When unable to copy the file into the data store.
     * @throws \Exception When unable to set the mode on the file in the data store.
     *
     * @return string The path to the file in the data store directory.
     */
    protected function addFileToDataStoreDirectory($fileUri, $datasetId, $storeFileName)
    {
        try {
            $dataStoreDirectory = $this->getDataStoreDirectory($datasetId);
        } catch (FileNotFoundException $e) {
            $dataStoreDirectory = $this->createDataStoreDirectory($datasetId);
        }
        $storeFilePath = "$dataStoreDirectory/$storeFileName";
        if (file_exists($storeFilePath)) {
            if (!unlink($storeFilePath)) {
                throw new \Exception("Could not delete existing file: $storeFilePath");
            }
        }
        if (!copy($fileUri, $storeFilePath)) {
            throw new \Exception("Could not copy $fileUri to $storeFilePath");
        }
        if (!chmod($storeFilePath, 0644)) {
            throw new \Exception("Could not set file mode on $storeFilePath");
        }
        $this->setOwnerGroupFacls($storeFilePath, 'custodian', 'custodian');
        return $storeFilePath;
    }

    /**
     * Create a link in the download directory.
     *
     * @param string $storeFilePath The path to the file in the data store.
     * @param string $datasetId     The id of the dataset.
     * @param string $storeFileName The name of the file in the data store.
     *
     * @throws \Exception When unable to delete an existing file in the download directory.
     * @throws \Exception When unable to create a link in the download directory..
     *
     * @return string The path to the file in the download directory.
     */
    protected function createLinkInDownloadDirectory($storeFilePath, $datasetId, $storeFileName)
    {
        try {
            $dataDownloadDirectory = $this->getDataDownloadDirectory($datasetId);
        } catch (FileNotFoundException $e) {
            $dataDownloadDirectory = $this->createDataDownloadDirectory($datasetId);
        }
        $downloadFilePath = "$dataDownloadDirectory/$storeFileName";
        if (file_exists($downloadFilePath)) {
            if (!unlink($downloadFilePath)) {
                throw new \Exception("Could not delete existing file: $downloadFilePath");
            }
        }
        if (!link($storeFilePath, $downloadFilePath)) {
            throw new \Exception("Could not link $downloadFilePath to $storeFilePath");
        }
        return $downloadFilePath;
    }

    /**
     * Get the data store directory for a dataset.
     *
     * @param string $datasetId The id of the dataset to get the data store directory for.
     *
     * @throws FileNotFoundException When the data store directory is not found.
     *
     * @return string
     */
    protected function getDataStoreDirectory($datasetId)
    {
        $dataStoreDirectory = "$this->dataStoreDirectory/$datasetId";
        if (!file_exists($dataStoreDirectory)) {
            throw new FileNotFoundException($dataStoreDirectory);
        }
        return $dataStoreDirectory;
    }

    /**
     * Create the data store directory for a dataset if it doesn't exist.
     *
     * @param string $datasetId The id of the dataset to create the data store directory for.
     *
     * @throws \Exception When an error occurs creating the data store directory.
     *
     * @return string
     */
    protected function createDataStoreDirectory($datasetId)
    {
        $dataStoreDirectory = "$this->dataStoreDirectory/$datasetId";
        if (!file_exists($dataStoreDirectory)) {
            if (!mkdir($dataStoreDirectory, 0750)) {
                throw new \Exception("Could not create $dataStoreDirectory");
            }
            $this->setOwnerGroupFacls($dataStoreDirectory, 'custodian', 'custodian', 'u:apache:--x');
        }
        return $dataStoreDirectory;
    }

    /**
     * Get the data download directory for a dataset.
     *
     * @param string $datasetId The id of the dataset to get the data download directory for.
     *
     * @throws FileNotFoundException When the data download directory is not found.
     *
     * @return string
     */
    protected function getDataDownloadDirectory($datasetId)
    {
        $dataDownloadDirectory = "$this->dataDownloadDirectory/$datasetId";
        if (!file_exists($dataDownloadDirectory)) {
            throw new FileNotFoundException($dataDownloadDirectory);
        }
        return $dataDownloadDirectory;
    }

    /**
     * Create the data download directory for a dataset if it doesn't exist.
     *
     * @param string  $datasetId  The id of the dataset to check the data download directory for.
     * @param boolean $restricted Whether or not the dataset is restricted.
     *
     * @throws \Exception When an error occurs creating the data download directory.
     *
     * @return string
     */
    protected function createDataDownloadDirectory($datasetId, $restricted = false)
    {
        $downloadDirectory = "$this->dataDownloadDirectory/$datasetId";
        if (!file_exists($downloadDirectory)) {
            if ($restricted) {
                $mode = 0750;
            } else {
                $mode = 0751;
            }
            if (!mkdir($downloadDirectory, $mode)) {
                throw new \Exception("Could not create $downloadDirectory");
            }
            $this->setOwnerGroupFacls($downloadDirectory, 'apache', 'apache', 'g:GRIIDC:r-x');
        }
        return $downloadDirectory;
    }

    /**
     * Set the owner, group, and FACLs for a file or directory.
     *
     * @param string $file  The file or directory to set owner, group, and FACLs for.
     * @param string $owner The owner to set.
     * @param string $group The group to set.
     * @param string $facls The FACLs to set.
     *
     * @throws \Exception When an error occurs setting the owner of the data download directory.
     * @throws \Exception When an error occurs setting the group of the data download directory.
     * @throws \Exception When an error occurs setting the FACLs of the data download directory.
     *
     * @return void
     */
    protected function setOwnerGroupFacls($file, $owner, $group, $facls = null)
    {
        if (!chown($file, $owner)) {
            throw new \Exception("Could not set owner to $owner for $file");
        }
        if (!chgrp($file, $group)) {
            throw new \Exception("Could not set group to $group for $file");
        }
        if (null !== $facls) {
            $output = array();
            exec("setfacl -m $facls $file", $output, $returnVal);
            if ($returnVal !== 0) {
                throw new \Exception("Could not set ACls to $facls for $file (Return value: $returnVal)");
            }
        }
    }

    /**
     * Get the name for a file in the data store.
     *
     * @param string $datasetId The id of the dataset to add the file to.
     * @param string $type      The type (dataset or metadata) of the file.
     *
     * @throws \Exception When the type is not valid.
     *
     * @return string
     */
    protected function getStoreFileName($datasetId, $type)
    {
        $storeFileName = "$datasetId.";
        switch ($type) {
            case 'dataset':
                $storeFileName .= 'dat';
                break;
            case 'metadata':
                $storeFileName .= 'met';
                break;
            default:
                throw new \Exception("$type is not a valid type");
        }
        return $storeFileName;
    }
}

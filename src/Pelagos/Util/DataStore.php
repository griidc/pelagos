<?php

namespace Pelagos\Util;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

use Pelagos\Exception\HtmlFoundException;

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
     * The POSIX user that will own files and directories in the data store.
     *
     * @var string
     */
    protected $dataStoreOwner;

    /**
     * The POSIX group for files and directories in the data store.
     *
     * @var string
     */
    protected $dataStoreGroup;

    /**
     * The POSIX group that contains users that can browse all of the data download directory.
     *
     * @var string
     */
    protected $dataDownloadBrowserGroup;

    /**
     * The POSIX user the web server runs under.
     *
     * @var string
     */
    protected $webServerUser;
    
    /**
     * Indicates the algorithm used to produce the MD hash.
     */
    const SHA256 = 'sha256';
    /**
     * Indicates that the type of the file type is a data file, not metadata.
     */
    const DATASET_FILE_TYPE = 'dataset';
    
    /**
     * Indicates that the type of the file named is metadata.
     */
    const METADATA_FILE_TYPE = 'metadata';
    /**
     * Constructor.
     *
     * @param string $dataStoreDirectory       The data store directory.
     * @param string $dataDownloadDirectory    The data download directory.
     * @param string $dataStoreOwner           The POSIX user that will own files and directories in the data store.
     * @param string $dataStoreGroup           The POSIX group for files and directories in the data store.
     * @param string $dataDownloadBrowserGroup The POSIX group that contains users that can
     *                                         browse all of the data download directory.
     * @param string $webServerUser            The POSIX user the web server runs under.
     */
    public function __construct(
        $dataStoreDirectory,
        $dataDownloadDirectory,
        $dataStoreOwner,
        $dataStoreGroup,
        $dataDownloadBrowserGroup,
        $webServerUser
    ) {
        $this->dataStoreDirectory = $dataStoreDirectory;
        $this->dataDownloadDirectory = $dataDownloadDirectory;
        $this->dataStoreOwner = $dataStoreOwner;
        $this->dataStoreGroup = $dataStoreGroup;
        $this->dataDownloadBrowserGroup = $dataDownloadBrowserGroup;
        $this->webServerUser = $webServerUser;
    }

    /**
     * Add a file to the data store.
     *
     * @param string $fileUri   The URI of the file to add.
     * @param string $datasetId The id of the dataset to add the file to.
     * @param string $type      The type (dataset or metadata) of the file.
     *
     * @throws \Exception         When the file URI is not set.
     * @throws \Exception         When the file does not exist.
     * @throws \Exception         When the file URI is http and file could not be downloaded.
     * @throws HtmlFoundException When HTML is found.
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
        if (preg_match('/^http/', $fileUri)) {
            $browser = new \Buzz\Browser();
            $result = $browser->head($fileUri);
            $status = $result->getHeaders()[0];
            if (!preg_match('/200/', $status)) {
                throw new \Exception("File could not be downloaded from $fileUri ($status)");
            }
            $contentType = $result->getHeader('Content-Type');
            if (preg_match('#^text/html#', $contentType)) {
                throw new HtmlFoundException("HTML file found at $fileUri");
            }
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
        $this->setOwnerGroupFacls($storeFilePath, $this->dataStoreOwner, $this->dataStoreGroup);
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
            $this->setOwnerGroupFacls(
                $dataStoreDirectory,
                $this->dataStoreOwner,
                $this->dataStoreGroup,
                'u:' . $this->webServerUser . ':--x'
            );
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
            $this->setOwnerGroupFacls(
                $downloadDirectory,
                $this->dataStoreOwner,
                $this->dataStoreGroup,
                'u:' . $this->webServerUser . ':r-x,' . 'g:' . $this->dataDownloadBrowserGroup . ':r-x'
            );
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
            case self::DATASET_FILE_TYPE:
                $storeFileName .= 'dat';
                break;
            case self::METADATA_FILE_TYPE:
                $storeFileName .= 'met';
                break;
            default:
                throw new \Exception("$type is not a valid type");
        }
        return $storeFileName;
    }
}

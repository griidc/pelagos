<?php

namespace Pelagos\Util;

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
     * @param string $filePath The path to the file to add.
     * @param string $udi      The UDI of the dataset to add the file to.
     * @param string $type     The type (data or metadata) of the file.
     *
     * @throws \Exception When the file does not exist.
     * @throws \Exception When the type is not valid.
     * @throws \Exception When unable to copy the file into the data store.
     * @throws \Exception When unable to set the mode on the file in the data store.
     *
     * @return void
     */
    public function addFile($filePath, $udi, $type)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File: $filePath not found!");
        }
        $destination = "$this->dataStoreDirectory/$udi/$udi.";
        switch ($type) {
            case 'data':
                $destination .= 'dat';
                break;
            case 'metadata':
                $destination .= 'met';
                break;
            default:
                throw new \Exception("$type is not a vlid type");
        }
        $this->checkDataStoreDirectory($udi);
        $this->checkDataDownloadDirectory($udi);
        if (!copy($filePath, $destination)) {
            throw new \Exception("Could not copy $filePath to $destination");
        }
        if (!chmod($destination, 0644)) {
            throw new \Exception("Could not set file mode on $destination");
        }
        $this->setOwnerGroupFacls($destination, 'custodian', 'custodian');
        echo "Adding $filePath to $destination\n";
    }

    /**
     * Check that the data store directory for a dataset exists and create it if it doesn't.
     *
     * @param string $udi The UDI of the dataset to check the data store directory for.
     *
     * @throws \Exception When an error occurs creating the data store directory.
     *
     * @return void
     */
    public function checkDataStoreDirectory($udi)
    {
        $dataStoreDirectory = "$this->dataStoreDirectory/$udi";
        if (!file_exists($dataStoreDirectory)) {
            if (!mkdir($dataStoreDirectory, 0750)) {
                throw new \Exception("Could not create $dataStoreDirectory");
            }
            $this->setOwnerGroupFacls($dataStoreDirectory, 'custodian', 'custodian', 'u:apache:--x');
        }
    }

    /**
     * Check that the data download directory for a dataset exists and create it if it doesn't.
     *
     * @param string  $udi        The UDI of the dataset to check the data download directory for.
     * @param boolean $restricted Whether or not the dataset is restricted.
     *
     * @throws \Exception When an error occurs creating the data download directory.
     *
     * @return void
     */
    public function checkDataDownloadDirectory($udi, $restricted = false)
    {
        $downloadDirectory = "$this->dataDownloadDirectory/$udi";
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
}

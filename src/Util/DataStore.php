<?php

namespace App\Util;

use GuzzleHttp\Client as GuzzleClient;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

use App\Exception\HtmlFoundException;

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
     * Username for anonymous FTP servers.
     *
     * @var string
     */
    protected $anonFtpUser;

    /**
     * Password string to use for anonymous FTP servers.
     *
     * @var string
     */
    protected $anonFtpPass;

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
     * @param string $anonFtpUser              The username to use on anonymous ftp servers.
     * @param string $anonFtpPass              The password (email) to send to anonymous ftp servers.
     */
    public function __construct(
        string $dataStoreDirectory,
        string $dataDownloadDirectory,
        string $dataStoreOwner,
        string $dataStoreGroup,
        string $dataDownloadBrowserGroup,
        string $webServerUser,
        string $anonFtpUser,
        string $anonFtpPass
    ) {
        $this->dataStoreDirectory = $dataStoreDirectory;
        $this->dataDownloadDirectory = $dataDownloadDirectory;
        $this->dataStoreOwner = $dataStoreOwner;
        $this->dataStoreGroup = $dataStoreGroup;
        $this->dataDownloadBrowserGroup = $dataDownloadBrowserGroup;
        $this->webServerUser = $webServerUser;
        $this->anonFtpUser = $anonFtpUser;
        $this->anonFtpPass = $anonFtpPass;
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
     * @throws HtmlFoundException When HTML is found but not properly declared in the HTTP header.
     *
     * @return string The original file name of the added file.
     */
    public function addFile(string $fileUri, string $datasetId, string $type)
    {
        if (null === $fileUri) {
            throw new \Exception("$type file URI not set");
        }
        if (preg_match('#^(file://|/)#', $fileUri) and !file_exists($fileUri)) {
            throw new \Exception("File: $fileUri not found!");
        }
        // Default to the base name of the file URI.
        $fileName = basename($fileUri);
        if (preg_match('/^http/', $fileUri)) {
            // Decode any characters escaped in the URL.
            $fileName = urldecode($fileName);
            $client = new GuzzleClient();
            $result = $client->request('HEAD', $fileUri);
            $status = $result->getStatusCode();
            if (200 !== $status) {
                throw new \Exception("File could not be downloaded from $fileUri ($status)");
            }
            $contentType = $result->getHeader('Content-Type');
            if (!empty($contentType) and preg_match('#^text/html#', $contentType[0])) {
                throw new HtmlFoundException("HTML file found at $fileUri");
            }
            $contentDisposition = $result->getHeader('Content-Disposition');
            // Match quoted or unquoted file names.
            if (!empty($contentDisposition) and preg_match('/^attachment;\s*filename=(?:"([^"]+)"|(.+))$/', $contentDisposition[0], $matches)) {
                if (!empty($matches[1])) {
                    // We found a quoted file name.
                    $fileName = $matches[1];
                } elseif (!empty($matches[2])) {
                    // We found an unquoted file name.
                    $fileName = $matches[2];
                }
            }
        }
        $storeFileName = $this->getStoreFileName($datasetId, $type);
        $storeFilePath = $this->addFileToDataStoreDirectory($fileUri, $datasetId, $storeFileName);
        if (preg_match('/^http/', $fileUri) and mime_content_type($storeFilePath) === 'text/html') {
            // If the HTTP header Content-Type check above failed to detect an html file.
            throw new HtmlFoundException("HTML file found at $fileUri");
        }
        $this->createLinkInDownloadDirectory($storeFilePath, $datasetId, $storeFileName);
        return $fileName;
    }

    /**
     * Get the info for a file in the data store.
     *
     * @param string $datasetId The id of the dataset to add the file to.
     * @param string $type      The type (dataset or metadata) of the file.
     *
     * @return File
     */
    public function getFileInfo(string $datasetId, string $type)
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
    public function getDownloadFileInfo(string $datasetId, string $type)
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
    protected function addFileToDataStoreDirectory(string $fileUri, string $datasetId, string $storeFileName)
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
        // Insert best-practice anonymous FTP credentials, per RFC1635 (https://www.rfc-editor.org/rfc/rfc1635.txt)
        if (preg_match('/^ftp:\/\//i', $fileUri)) {
            $hostAndFile = preg_replace('/^ftp:\/\//i', '', $fileUri);
            $fileUri = 'ftp://' . $this->anonFtpUser . ':' . $this->anonFtpPass . '@' . $hostAndFile;
        }
        if (!copy($fileUri, $storeFilePath)) {
            throw new \Exception("Could not copy $fileUri to $storeFilePath");
        }
        if (!chmod($storeFilePath, 0644)) {
            throw new \Exception("Could not set file mode on $storeFilePath");
        }
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
    protected function createLinkInDownloadDirectory(string $storeFilePath, string $datasetId, string $storeFileName)
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
    protected function getDataStoreDirectory(string $datasetId)
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
    protected function createDataStoreDirectory(string $datasetId)
    {
        $dataStoreDirectory = "$this->dataStoreDirectory/$datasetId";
        if (!file_exists($dataStoreDirectory)) {
            if (!mkdir($dataStoreDirectory, 0750)) {
                throw new \Exception("Could not create $dataStoreDirectory");
            }
            $this->setFacls(
                $dataStoreDirectory,
                'A::' . $this->getIdFromName($this->webServerUser) . ':X'
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
    protected function getDataDownloadDirectory(string $datasetId)
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
    protected function createDataDownloadDirectory(string $datasetId, bool $restricted = false)
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
            $this->setFacls(
                $downloadDirectory,
                'A::' . $this->getIdFromName($this->webServerUser) . ':RX,' .
                    'A:g:' . $this->getIdFromName($this->dataDownloadBrowserGroup, true) . ':RX'
            );
        }
        return $downloadDirectory;
    }

    /**
     * Set the NFS4 FACLs for a file or directory.
     *
     * @param string $file  The file or directory to set owner, group, and FACLs for.
     * @param string $facls The FACLs to set.
     *
     * @throws \Exception When an error occurs setting the NFS4 FACLs of the data download directory.
     *
     * @return void
     */
    protected function setFacls(string $file, string $facls = null)
    {
        if (null !== $facls) {
            $output = array();
            exec("nfs4_setfacl -a $facls $file", $output, $returnVal);
            if ($returnVal !== 0) {
                throw new \Exception("Could not set NFS4 ACls to $facls for $file (Return value: $returnVal)");
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
    protected function getStoreFileName(string $datasetId, string $type)
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

    /**
     * Get the numeric ID of a POSIX user or group.
     *
     * @param string  $name    The user or group name.
     * @param boolean $isGroup Flag set to true if a group, defaults to user.
     *
     * @throws \Exception When username can not be resolved.
     * @throws \Exception When groupname can not be resolved.
     *
     * @return string
     */
    protected function getIdFromName(string $name, bool $isGroup = false)
    {
        if ($isGroup) {
            // These calls returns false on failure to resolve.
            $obj = posix_getgrnam($name);
            if (false === $obj) {
                throw new \Exception('Cannot resolve GID for groupname.');
            } else {
                $id = $obj['gid'];
            }
        } else {
            $obj = posix_getpwnam($name);
            if (false === $obj) {
                throw new \Exception('Cannot resolve UID for username.');
            } else {
                $id = $obj['uid'];
            }
        }
        return $id;
    }
}

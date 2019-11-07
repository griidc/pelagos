<?php

namespace Pelagos\Bundle\AppBundle\Handler;

use Symfony\Component\HttpFoundation\Request;

/**
 * A handler for uploads.
 *
 * This is based on https://github.com/FineUploader/php-traditional-server/blob/master/handler.php
 */
class UploadHandler
{
    /**
     * A list of allowed file extensions.
     *
     * Default: all extensions are allowed.
     *
     * @var array
     */
    protected $allowedExtensions = array();

    /**
     * The size limit for uploaded files.
     *
     * Default: no size limit.
     *
     * @var integer
     */
    protected $sizeLimit = null;

    /**
     * The name of the form input field.
     *
     * @var string
     */
    protected $inputName = 'qqfile';

    /**
     * The directory where uploaded files will be placed.
     *
     * @var string
     */
    protected $uploadDirectory;

    /**
     * The directory where file chunks will be stored.
     *
     * @var string
     */
    protected $chunksDirectory;

    /**
     * The probability to use for deciding when to garbage collect old chunks.
     *
     * Dedfault: once in 1000 requests.
     *
     * @var float
     */
    protected $chunksCleanupProbability = 0.001;

    /**
     * The number of seconds after which chunks will be removed.
     *
     * Default: one week.
     *
     * @var integer
     */
    protected $chunksExpireIn = 604800;

    /**
     * Constructor.
     *
     * @param string $homedirPrefix The full path prefix for home directories.
     */
    public function __construct($homedirPrefix)
    {
        $this->uploadDirectory = $homedirPrefix . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'files';
        $this->chunksDirectory = $homedirPrefix . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'chunks';
    }

    /**
     * Get the original filename.
     *
     * @param Request $request The Symfony request object.
     *
     * @return string
     */
    public function getName(Request $request)
    {
        if (null !== $request->request->get('qqfilename')) {
            return $request->request->get('qqfilename');
        }

        if (null !== $request->files->get($this->inputName)) {
            return $request->files->get($this->inputName)->getClientOriginalName();
        }
    }

    /**
     * Combine file chunks into uploaded file.
     *
     * @param Request $request The Symfony request object.
     *
     * @return array
     */
    public function combineChunks(Request $request)
    {
        $uuid = $request->request->get('qquuid');
        $name = $this->getName($request);
        $targetFolder = $this->chunksDirectory . DIRECTORY_SEPARATOR . $uuid;
        if (null !== $request->request->get('qqtotalparts')) {
            $totalParts = (int) $request->request->get('qqtotalparts');
        } else {
            $totalParts = 1;
        }

        $targetPath = join(DIRECTORY_SEPARATOR, array($this->uploadDirectory, $uuid, $name));

        if (!file_exists($targetPath)) {
            mkdir(dirname($targetPath), 0755, true);
        }
        $target = fopen($targetPath, 'wb');

        for ($i = 0; $i < $totalParts; $i++) {
            $chunk = fopen($targetFolder . DIRECTORY_SEPARATOR . $i, 'rb');
            stream_copy_to_stream($chunk, $target);
            fclose($chunk);
        }

        // Success
        fclose($target);

        for ($i = 0; $i < $totalParts; $i++) {
            unlink($targetFolder . DIRECTORY_SEPARATOR . $i);
        }

        rmdir($targetFolder);

        if (null !== $this->sizeLimit && filesize($targetPath) > $this->sizeLimit) {
            unlink($targetPath);
            http_response_code(413);
            return array(
                'success' => false,
                'uuid' => $uuid,
                'preventRetry' => true
            );
        }

        return array(
            'success' => true,
            'uuid' => $uuid,
            'name' => $name,
            'path' => $targetPath,
        );
    }

    /**
     * Process the upload.
     *
     * @param Request $request The Symfony request object.
     *
     * @return array
     */
    public function handleUpload(Request $request)
    {
        if (null !== $request->query->get('done')) {
            return $this->combineChunks($request);
        }

        if (is_writable($this->chunksDirectory) and
            1 == mt_rand(1, (1 / $this->chunksCleanupProbability))) {
            // Run garbage collection
            $this->cleanupChunks();
        }

        // Check that the max upload size specified in class configuration does not
        // exceed size allowed by server config
        if ($this->toBytes(ini_get('post_max_size')) < $this->sizeLimit ||
            $this->toBytes(ini_get('upload_max_filesize')) < $this->sizeLimit) {
            $neededRequestSize = max(1, ($this->sizeLimit / 1024 / 1024)) . 'M';
            return array(
                'error' => 'Server error. Increase post_max_size and upload_max_filesize to ' .
                           $neededRequestSize
            );
        }

        if ($this->isInaccessible($this->uploadDirectory)) {
            return array('error' => "Server error. Uploads directory ($this->uploadDirectory) isn't writable");
        }

        $type = $request->server->get('CONTENT_TYPE');
        if (null !== $request->server->get('HTTP_CONTENT_TYPE')) {
            $type = $request->server->get('HTTP_CONTENT_TYPE');
        }

        if (!isset($type)) {
            return array('error' => 'No files were uploaded.');
        } elseif (strpos(strtolower($type), 'multipart/') !== 0) {
            return array(
                'error' => 'Server error. Not a multipart request. ' .
                           'Please set forceMultipart to default value (true).'
            );
        }

        // Get size and name
        $file = $request->files->get($this->inputName);
        $size = $file->getSize();
        if (null !== $request->request->get('qqtotalfilesize')) {
            $size = $request->request->get('qqtotalfilesize');
        }

        $name = $this->getName($request);

        // check file error
        if (0 !== $file->getError()) {
            return array('error' => 'Upload Error #' . $file->getError());
        }

        // Validate name
        if ($name === null || $name === '') {
            return array('error' => 'File name empty.');
        }

        // Validate file size
        if ($size == 0) {
            return array('error' => 'File is empty.');
        }

        if (null !== $this->sizeLimit && $size > $this->sizeLimit) {
            return array('error' => 'File is too large.', 'preventRetry' => true);
        }

        // Validate file extension
        $pathinfo = pathinfo($name);
        $ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';

        if ($this->allowedExtensions and
            !in_array(strtolower($ext), array_map('strtolower', $this->allowedExtensions))) {
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of ' . $these . '.');
        }

        // Save a chunk
        if (null !== $request->request->get('qqtotalparts')) {
            $totalParts = (int) $request->request->get('qqtotalparts');
        } else {
            $totalParts = 1;
        }

        $uuid = $request->request->get('qquuid');
        if ($totalParts > 1) {
            // chunked upload

            $partIndex = (int) $request->request->get('qqpartindex');

            if (!is_writable($this->chunksDirectory) && !is_executable($this->chunksDirectory)) {
                return array('error' => 'Server error. Chunks directory isn\'t writable or executable.');
            }

            $targetFolder = $this->chunksDirectory . DIRECTORY_SEPARATOR . $uuid;

            if (!file_exists($targetFolder)) {
                mkdir($targetFolder, 0755, true);
            }

            $file->move($targetFolder, $partIndex);

            return array(
                'success' => true,
                'uuid' => $uuid,
            );
        } else {
            // non-chunked upload
            $targetDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $uuid;

            if (!file_exists($targetDirectory)) {
                mkdir($targetDirectory, 0755, true);
            }
            
            $movedFile = $file->move($targetDirectory, $name);

            return array(
                'success' => true,
                'uuid' => $uuid,
                'name' => $name,
                'path' => $movedFile->getRealPath(),
            );
        }
    }

    /**
     * Process a delete request.
     *
     * @param string $uuid The UUID of the file to delete.
     *
     * @return array
     */
    public function handleDelete($uuid)
    {
        if ($this->isInaccessible($this->uploadDirectory)) {
            return array('error' => 'Server error. Uploads directory isn\'t writable and executable.');
        }

        $targetFolder = $this->uploadDirectory;

        $target = join(DIRECTORY_SEPARATOR, array($targetFolder, $uuid));

        if (is_dir($target)) {
            $this->removeDir($target);
            return array(
                'success' => true,
                'uuid' => $uuid
            );
        } else {
            return array(
                'success' => false,
                'uuid' => $uuid,
                'error' => 'File not found! Unable to delete.',
            );
        }
    }

    /**
     * Deletes all file parts in the chunks folder for files uploaded more than chunksExpireIn seconds ago.
     *
     * @return void
     */
    protected function cleanupChunks()
    {
        foreach (scandir($this->chunksDirectory) as $item) {
            if ($item == '.' or $item == '..') {
                continue;
            }

            $path = $this->chunksDirectory . DIRECTORY_SEPARATOR . $item;

            if (!is_dir($path)) {
                continue;
            }

            if ((time() - filemtime($path)) > $this->chunksExpireIn) {
                $this->removeDir($path);
            }
        }
    }

    /**
     * Removes a directory and all files contained inside.
     *
     * @param string $dir The directory to remove.
     *
     * @return void
     */
    protected function removeDir($dir)
    {
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (is_dir($item)) {
                $this->removeDir($item);
            } else {
                unlink(join(DIRECTORY_SEPARATOR, array($dir, $item)));
            }
        }
        rmdir($dir);
    }

    /**
     * Converts a given size with units to bytes.
     *
     * @param string $str The string to convert.
     *
     * @return integer
     */
    protected function toBytes($str)
    {
        $val = preg_replace('/\D/', '', $str);
        $last = strtolower($str[(strlen($str) - 1)]);
        switch ($last) {
            case 'g':
                $val *= 1024;
                // Fall through to mb.
            case 'm':
                $val *= 1024;
                // Fall through to kb.
            case 'k':
                $val *= 1024;
        }
        return $val;
    }

    /**
     * Determines whether a directory can be accessed.
     *
     * @param string $directory The target directory to test access.
     *
     * @return boolean
     */
    protected function isInaccessible($directory)
    {
        return !is_writable($directory) or !is_executable($directory);
    }
}

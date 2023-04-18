<?php

namespace App\Util;

use Ramsey\Uuid\Uuid;

/**
 * Utilities for files.
 */
class FileNameUtilities
{
    /**
     * A friendly name for this type of entity.
     */
    const MAX_FILE_NAME_LENGTH = 255;

    /**
     * This method will shorten the basename is needed.
     *
     * @param string $fileName Filename that needs to be fixed.
     *
     * @return string
     */
    public static function fixFileNameLength(string $fileNamePath, int $maxFileNameLength = self::MAX_FILE_NAME_LENGTH): string
    {
        $pathinfo = pathinfo($fileNamePath);
        $filename = $pathinfo['filename'];
        $extension = $pathinfo['extension'] ?? '';
        $dirname = $pathinfo['dirname'];

        if ($dirname === '.') {
            $dirname = '';
        } else {
            $dirname .= '/';
        }

        if ($extension !== '') {
            $extension = '.' . $extension;
        }

        $filename = substr($filename, 0, $maxFileNameLength - strlen($extension));

        return $dirname . $filename . $extension;
    }

    /**
     * Makes a unique filename.
     *
     * @param string $fileName Filename that needs to be made unique.
     *
     * @return string
     */
    public static function makeFileName(string $fileName): string
    {
        $uuid = Uuid::uuid4()->toString();
        // add only last 5 bytes of uuid to the destination path
        $fileName .= '_' . substr($uuid, -5);

        return $fileName;
    }

    /**
     * Renames the file and adds a sequence to it.
     *
     * @param string $filePathName The file name to be renamed.
     *
     * @throws \Exception When the sequence if over 999.
     *
     * @return string The renamed filename string.
     */
    public static function renameFile(string $filePathName): string
    {
        $pathParts = pathinfo($filePathName);
        if ($pathParts['dirname'] === '.') {
            $dirname = '';
        } else {
            $dirname = $pathParts['dirname'] . DIRECTORY_SEPARATOR;
        }
        $fileName = $pathParts['filename'];
        $extension = $pathParts['extension'] ?? '';

        if (empty($pathParts['extension'])) {
            $extension = '';
        } else {
            $extension = '.' . $extension;
        }

        $patterns = array('/^(.*)\((\d{1,3})\)(\.?.*)$/','/(^(?:(?!\(\d{1,3}\)).)*$)()/');

        $fileName = preg_replace_callback(
            $patterns,
            function ($matches) {
                if ((int)$matches[2] >= 999) {
                    throw new \Exception('Can only rename up to 999 times!');
                }
                return $matches[1] . '(' . ((int)$matches[2] + 1) . ')';
            },
            $fileName
        );

        return $dirname . $fileName . $extension;
    }
}

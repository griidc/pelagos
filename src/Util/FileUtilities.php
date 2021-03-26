<?php

namespace App\Util;

use Ramsey\Uuid\Uuid;

/**
 * Utilities for files.
 */
class FileUtilities
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
    public static function fixFileNameLength(string $fileNamePath, $maxFileNameLength = self::MAX_FILE_NAME_LENGTH) : string
    {
        $pathinfo = pathinfo($fileNamePath);
        $filename = $pathinfo['filename'];
        $extension = $pathinfo['extension'];
        $dirname = $pathinfo['dirname'];

        if ($dirname === '.') {
            $dirname = '';
        } else {
            $dirname .= '/';
        }

        if (!isset($extension)) {
            $extension = '';
        } else {
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
    private function makeFileName(string $fileName) : string
    {
        $uuid = Uuid::uuid4()->toString();
        // add only last 5 bytes of uuid to the destination path
        $fileName .= '_' . substr($uuid, -5);

        return $fileName;
    }
}

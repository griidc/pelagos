<?php

namespace App\Util;

/**
 * Utilities for files.
 */
class FileUtilities
{
    /**
     * A friendly name for this type of entity.
     */
    const MAX_FILE_NAME_LENGTH = 256;
    
    /**
     * This method will shorten the basename is needed.
     *
     * @param string $fileName Filename that needs to be fixed.
     *
     * @return string
     */
    public static function fixFileName(string $fileNamePath, $maxFileNameLength = SELF::MAX_FILE_NAME_LENGTH) : string
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
        
        if (!isset($extension)) {
            $extension = '';
        } else {
            $extension = '.' . $extension;
        }
        
        $filename = substr($filename, 0, $maxFileNameLength - strlen($extension));
        
        return $dirname . $filename . $extension;
    }
}

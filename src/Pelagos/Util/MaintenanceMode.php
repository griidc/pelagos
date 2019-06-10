<?php

namespace Pelagos\Util;

/**
 * A utility to create and issue DOI from EZID API.
 */
class MaintenanceMode
{
    /**
     * Is the system in maintenance mode?
     *
     * boolean If in maintenance mode.
     */
    public static function isMaintenanceMode(string $path) : bool
    {
        $file = $path . '/../var/maintenance.ini';
        return file_exists($file);
    }
    
    
    
}
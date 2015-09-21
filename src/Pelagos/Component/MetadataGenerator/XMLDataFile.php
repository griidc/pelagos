<?php

namespace Pelagos\Component\MetadataGenerator;

use \Pelagos\Exception\NotFoundException;

/**
 * A class to get XML metadata files from a file.
 *
 * This pulls an XML metadata file from filesystem
 * and returns it as a string.  The file location is
 * determined by the UDI passed.
 */
class XMLDataFile
{
    /**
     * This variable contains the instanciated instance of the class.
     *
     * @var XMLDataFile $instance
     */
    private static $instance = null;

    /**
     * Singleton implementation.
     *
     * Only one instance of this class allowed
     * per executable unit.
     */
    private function __construct()
    {
    }
    /**
     * Singleton implementation.
     *
     * @return XMLDataFile self::$instance Returns instance of XMLDataFile
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new XMLDataFile();
        }
        return self::$instance;
    }

    /**
     * This function resolves a filesystem location from an UDI.
     *
     * @param string $udi Dataset identification.
     *
     * @return string Absolute filesystem location of metadata file associated with supplied UDI.
     */
    private function getFileLocation($udi)
    {
        // Load global pelagos config.
        $GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

        // Load the Common library.
        require_once 'Common.php';

        // Check for local config file.
        if (file_exists(__DIR__ . '/../config.ini')) {
            // Merge local config with global config.
            $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file(__DIR__ . '/../config.ini', true));
        }

        $metadataPath = $GLOBALS['config']['paths']['data_download'];
        $filePath = $metadataPath . '/' . $udi . '/' . $udi . '.met';

        return $filePath;
    }

    /**
     * Get the location of the xml file from the filesystem.
     *
     * Returns location as string xor will throw NotFoundException
     * if file is not found or not readable.  If XML is not valid,
     * it will allow the InvalidXmlException to bubble up from
     * the Validator class.
     *
     * @param string $udi Dataset identifier.
     *
     * @return boolean|string Result.
     *
     * @throws NotFoundException If not found.
     */
    public function getXML($udi)
    {
        $targetUdi = trim($udi);
        $xmlText = false;
        $path = $this->getFileLocation($targetUdi);
        if ($path == false) {
            throw new NotFoundException('XMLDataFile No XML found in path: ' . $path);
        } elseif (is_readable($path)) {
            $xmlText = file_get_contents($path);
            if ($xmlText === false) {
                throw new NotFoundException('XMLDataFile file_get_contents is FALSE for path: ' . $path);
            }
            $validator = new XMLValidator();
            // Throws InvalidXmlException.
            $validator->validate($xmlText);
            return $xmlText;
        }
        throw new NotFoundException('XMLDataFile No XML found in path: ' . $path);
    }
}

<?php
namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;

/**
 * This is a Metadata utility class.
 */
class Metadata
{
    /**
     * This is a \SimpleXML object of Metadata.
     *
     * @var \SimpleXml
     */
    protected $metadata;

    /**
     * Validates a SimpleXML object against a schema.
     *
     * @param \SimpleXml $metadata A XML doc to be validated.
     * @param string     $schema   URL of Schema used to validate.
     *
     * @throws \Exception When simpleXml not able to be converted to DomXML.
     *
     * @return array
     */
    public function validateIso(
        \SimpleXml $metadata,
        $schema = 'http://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd'
    ) {
        $domDoc = dom_import_simplexml($metadata);
        if (!$domDoc) {
            throw new \Exception('Could not convert SimpleXML into DomXML');
        }

        if (!$domDoc->schemaValidate($schema)) {
            $xmlErrors = libxml_get_errors();
            $errorList = array();
            $warningList = array();
            libxml_clear_errors();
            for ($i = 0; $i < count($xmlErrors); $i++) {
                switch ($xmlErrors[$i]->level) {
                    case LIBXML_ERR_WARNING:
                        $error = 'WARNING (' . $xmlErrors[$i]->code . ') on XML line ';
                        $error .= $xmlErrors[$i]->line . ': ' . $xmlErrors[$i]->message;
                        $warningList[] = $error;
                        break;
                    case LIBXML_ERR_ERROR:
                        $schemaErrors++;
                        $error = 'ERROR (' . $xmlErrors[$i]->code . ') on XML line ';
                        $error .= $xmlErrors[$i]->line . ': ' . $xmlErrors[$i]->message;
                        $errorList[] = $error;
                        break;
                    case LIBXML_ERR_FATAL:
                        $schemaErrors++;
                        $error = 'FATAL ERROR (' . $xmlErrors[$i]->code . ') on XML line ';
                        $error .= $xmlErrors[$i]->line . ': ' . $xmlErrors[$i]->message;
                        $errorList[] = $error;
                        break;
                }
            }
        }

        if (0 === count($errorList)) {
            $isoValid = true;
        } else {
            $isoValid = false;
        }

        $return = array(
            'valid' => $isoValid,
            'errors' => $errorList,
            'warnings' => $warningList
        );

        return $return;
    }
}

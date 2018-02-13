<?php
namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;

/**
 * This is a Metadata utility class.
 */
class Metadata
{
    /**
     * Validates XML against a schema.
     *
     * @param string $xml    Metadata as a XML string.
     * @param string $schema An optional URL of Schema used to validate.
     *
     * @return array of (validity boolean, array of errors, and array of warnings).
     */
    public function validateIso($xml, $schema = 'https://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd')
    {
        $errorList = array();
        $warningList = array();

        $domDoc = new \DomDocument('1.0', 'UTF-8');
        $tmpp = @$domDoc->loadXML($xml);
        if (!$tmpp) {
            $error = 'Could not parse as XML: ' . libxml_get_last_error()->message;
            $errorList[] = $error;
        }

        if (0 === count($errorList)) {
            libxml_use_internal_errors(true);
            if (false === $domDoc->schemaValidate($schema)) {
                $xmlErrors = libxml_get_errors();
                libxml_clear_errors();
                for ($i = 0; $i < count($xmlErrors); $i++) {
                    switch ($xmlErrors[$i]->level) {
                        case LIBXML_ERR_WARNING:
                            $error = 'WARNING (' . $xmlErrors[$i]->code . ') on XML line ';
                            $error .= $xmlErrors[$i]->line . ': ' . $xmlErrors[$i]->message;
                            $warningList[] = $error;
                            break;
                        case LIBXML_ERR_ERROR:
                            $error = 'ERROR (' . $xmlErrors[$i]->code . ') on XML line ';
                            $error .= $xmlErrors[$i]->line . ': ' . $xmlErrors[$i]->message;
                            $errorList[] = $error;
                            break;
                        case LIBXML_ERR_FATAL:
                            $error = 'FATAL ERROR (' . $xmlErrors[$i]->code . ') on XML line ';
                            $error .= $xmlErrors[$i]->line . ': ' . $xmlErrors[$i]->message;
                            $errorList[] = $error;
                            break;
                    }
                }
            }
        }

        if (0 === count($errorList)) {
            $isoValid = true;
        } else {
            $isoValid = false;
        }

        $return = array(
            'validity' => $isoValid,
            'errors' => $errorList,
            'warnings' => $warningList
        );

        return $return;
    }
}

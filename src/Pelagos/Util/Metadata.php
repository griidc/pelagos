<?php
namespace Pelagos\Util;

use Doctrine\ORM\EntityManager;

/**
 * This is a Metadata utility class.
 */
class Metadata
{
    /**
     * Metadata XML.
     *
     * @var string
     */
    protected $metadata;

    /**
     * Setter for metadata.
     *
     * @param string $metadata Metadata as XML text.
     *
     * @return void
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Validates XML as SimpleXMLElement object against a schema.
     *
     * @param string $schema An optional URL of Schema used to validate.
     *
     * @throws \Exception When metadata is not set.
     * @throws \Exception When XML not able to be converted to DomXML.
     *
     * @return array
     */
    public function validateIso($schema = 'http://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd')
    {
        if (null === $this->metadata) {
            throw new \Exception('Metadata must be populated to use this method.');
        }

        $domDoc = new \DomDocument('1.0', 'UTF-8');
        $tmpp = @$domDoc->loadXML($this->metadata);
        if (!$tmpp) {
            $err = libxml_get_last_error();
            $errStr = $err->message;
            throw new \Exception(
                "Malformed XML: XML could not be parsed by DomDoc. ($errStr)"
            );
        }

        $errorList = array();
        $warningList = array();
        if (!$domDoc->schemaValidate($schema)) {
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
            'validity' => $isoValid,
            'errors' => $errorList,
            'warnings' => $warningList
        );

        return $return;
    }
}

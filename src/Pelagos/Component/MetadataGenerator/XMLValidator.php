<?php


namespace Pelagos\Component\MetadataGenerator;

use \Pelagos\Exception\InvalidXmlException;

/**
 * This class validates a string against a schema.
 *
 * This class performs ISO-19115-2 validation against a passed
 * XML string.  It either returns a boolean true or throws
 * an exception.
 */
class XMLValidator
{
    /**
     * This method validates a string of XML.
     *
     * @param string $raw_xml This is XML as text.
     *
     * @return bool In the event of success.
     *
     * @throws InvalidXmlException If validation fails.
     */
    public function validate($raw_xml)
    {
        $errors = 0;

        // create domdoc element and attempt to populate with supplied XML
        libxml_use_internal_errors(true);
        $doc = new \DomDocument('1.0', 'UTF-8');
        $tmpp = @$doc->loadXML($raw_xml);
        if (!$tmpp) {
            $errors++;
        }

        // attempt to validate XML per ISO-19115-2
        $schema = 'http://www.ngdc.noaa.gov/metadata/published/xsd/schema.xsd';
        if (!$doc->schemaValidate($schema)) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            for ($i = 0; $i < sizeof($errors); $i++) {
                switch ($errors[$i]->level) {
                    case LIBXML_ERR_WARNING:
                        break;
                    case LIBXML_ERR_ERROR:
                        $errors++;
                        break;
                    case LIBXML_ERR_FATAL:
                        $errors++;
                        break;
                }
            }
        }
        if ($errors == 0) {
            return true;
        }
        throw new InvalidXmlException("Invalid XML found by XMLValidator");
    }
}

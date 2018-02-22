<?php
namespace Pelagos\Util;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

/**
 * This is a Metadata utility class.
 */
class Metadata
{
    /**
     * The twig *ml render.
     *
     * @var mixed
     */
    protected $twig;

    /**
     * Class constructor for dependency injection.
     *
     * @param mixed $twig The twig rendering engine.
     */
    public function __construct($twig)
    {
        $this->twig = $twig;
    }

    /**
     * Creates and returns an ISO-19115-2 XML representation of metadata as a string.
     *
     * @param Dataset $dataset The Pelagos Dataset to generate ISO metadata for.
     *
     * @return string||null of generated XML metadata.
     */
    public function getXmlRepresentation(Dataset $dataset)
    {
        $xml = null;
        if ($dataset->getDatasetSubmission() instanceof DatasetSubmission) {
            $xml = $this->twig->render(
                'PelagosAppBundle:MetadataGenerator:MI_Metadata.xml.twig',
                array(
                    'dataset' => $dataset,
                    'metadataFilename' => preg_replace('/:/', '-', $dataset->getUdi()) . '-metadata.xml',
                )
            );
            $tidyXml = new \tidy;
            $tidyXml->parseString(
                $xml,
                array(
                    'input-xml' => true,
                    'output-xml' => true,
                    'indent' => true,
                    'indent-spaces' => 4,
                    'wrap' => 0,
                ),
                'utf8'
            );
            $xml = $tidyXml;
            // Remove extra whitespace added around CDATA tags by tidy.
            $xml = preg_replace('/>[\s]+<\!\[CDATA\[/', '><![CDATA[', $xml);
            $xml = preg_replace('/]]>\s+</', ']]><', $xml);
        }
        return $xml;
    }

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

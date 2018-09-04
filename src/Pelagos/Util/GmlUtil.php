<?php
namespace Pelagos\Util;

/**
 * This is a utility class for dealing with GML manipulation.
 */
class GmlUtil
{
    /**
     * This function add namespace for validation to the given gml.
     *
     * @param string $gml        Gml that needs namespace.
     * @param array  $namespaces Array of attributes and values.
     *
     * @return string GML string with namespace.
     */
    public static function addNamespace($gml, array $namespaces)
    {
        $doc = new \DomDocument('1.0', 'UTF-8');

        $doc->loadXML($gml, LIBXML_NOERROR);
        $rootNode = $doc->documentElement;
        if (null !== $rootNode) {
            foreach ($namespaces as $key => $value) {
                $rootNode->setAttribute($key, $value);
            }
            $gml = $doc->saveXML();
            $cleanXML = new \SimpleXMLElement($gml, LIBXML_NOERROR);
            $dom = dom_import_simplexml($cleanXML);
            $gml = $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
        } else {
            //append namespaces to string using regex
            $strNameSpaces = '';
            foreach ($namespaces as $key => $value) {
                $strNameSpaces .= ' ' . $key . '="' . $value . '"';
            }
            $regEx = '/^<gml:\S*/';
            $gml = preg_replace($regEx, "$0$strNameSpaces", $gml);
        }
        return $gml;
    }
}

<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * A controller that does GML conversion.
 */
class GmlController extends Controller
{
    /**
     * Converting gml to wkt.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/gmltowkt")
     *
     * @Method("POST")
     *
     * @throws BadRequestHttpException When no GML is given.
     *
     * @return Response A response containing converted wkt.
     */
    public function toWktAction(Request $request)
    {
        $gml = $request->request->get('gml');

        if (!empty($gml)) {
            $query = 'SELECT ST_asText(ST_GeomFromGML(:gml, 4326));';
            $connection = $this->getDoctrine()->getManager()->getConnection();
            $statement = $connection->prepare($query);
            $statement->bindValue('gml', $gml);
            try {
                $statement->execute();
            } catch(\Exception $e) {
                return new Response(
                    $e->getMessage(),
                    Response::HTTP_BAD_REQUEST,
                    array('content-type' => 'text/plain')
                );
            }
            $results = $statement->fetchAll();
            $wkt = $results[0]['st_astext'];
            return new Response(
                $wkt,
                Response::HTTP_OK,
                array('content-type' => 'text/plain')
            );
        } else {
            throw new BadRequestHttpException('No GML given. (Parameter:gml)');
        }
    }

    /**
     * Converting wkt to gml.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/wkttogml")
     *
     * @Method("POST")
     *
     * @throws BadRequestHttpException When no WKT is given.
     *
     * @return Response A response containing converted gml.
     */
    public function fromWktAction(Request $request)
    {
        $wkt = $request->request->get('wkt');

        if (!empty($wkt)) {
            $query = 'SELECT ST_asGML(3,ST_GeomFromText(:wkt,4326),5,17)';
            $connection = $this->getDoctrine()->getManager()->getConnection();
            $statement = $connection->prepare($query);
            $statement->bindValue('wkt', $wkt);
            $statement->execute();
            $results = $statement->fetchAll();
            $gml = $results[0]['st_asgml'];
            $gml = $this->addGMLid($gml);

            return new Response(
                $gml,
                Response::HTTP_OK,
                array('content-type' => 'text/plain')
            );
        } else {
            throw new BadRequestHttpException('No Well Know Text given. (Parameter:wkt)');
        }
    }

    /**
     * This function add an ID to gml a feature.
     *
     * @param string $gml GML that needs ID added.
     *
     * @return string
     */
    private function addGMLid($gml)
    {
        $doc = new \DomDocument('1.0', 'UTF-8');
        $doc->loadXML($gml, LIBXML_NOERROR);

        foreach ($doc->childNodes as $node) {
            $topNode = $node->nodeName;
            switch ($topNode) {
                case 'gml:Polygon':
                    $node->setAttribute('gml:id', 'Polygon1');
                    break;
                case 'gml:Curve':
                    $node->setAttribute('gml:id', 'Curve1');
                    break;
                case 'gml:Point':
                    $node->setAttribute('gml:id', 'Point1');
                    break;
                case 'gml:MultiPoint':
                    $node->setAttribute('gml:id', 'Multipoint1');
                    $i = 0;
                    foreach ($node->childNodes as $child) {
                        $i++;
                        $child->firstChild->setAttribute('gml:id', "Point$i");
                    }
                    break;
                case 'gml:MultiCurve':
                    $node->setAttribute('gml:id', 'MultiCurve1');
                    $i = 0;
                    foreach ($node->childNodes as $child) {
                        $i++;
                        $child->firstChild->setAttribute('gml:id', "Curve$i");
                    }
                    break;
                case 'gml:MultiSurface':
                    $node->setAttribute('gml:id', 'MultiSurface');
                    $i = 0;
                    foreach ($node->childNodes as $child) {
                        $i++;
                        $child->firstChild->setAttribute('gml:id', "Polygon$i");
                    }
                    break;
            }
        }

        $gml = $doc->saveXML();
        $cleanXML = new \SimpleXMLElement($gml, LIBXML_NOERROR);
        $dom = dom_import_simplexml($cleanXML);
        $gml = $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
        return $gml;
    }

    /**
    * This function add namespace for validation to the given gml.
    *
    * @param string $gml GML that needs namespace.
    * @param array  $namespaces Array of attributes and values
    *
    * @return string
    */
    private function addNamespace($gml, $namespaces)
    {
        $doc = new \DomDocument('1.0', 'UTF-8');
        $doc->loadXML($gml, LIBXML_NOERROR);
        $rootNode = $doc->documentElement;
        foreach($namespaces as $key => $value) {
            $rootNode->setAttribute($key, $value);
        }

        $gml = $doc->saveXML();
        $cleanXML = new \SimpleXMLElement($gml, LIBXML_NOERROR);
        $dom = dom_import_simplexml($cleanXML);
        $gml = $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
        return $gml;
    }

    /**
     * This function validate Gml against iso schema
     *
     * @param Request $request The Symfony request object.
     * @param String $schema Url to remote schema validation cache.
     *
     * @Method("POST")
     *
     * @Route("/validategml")
     *
     * @throws BadRequestHttpException When no gml is given.
     *
     * @return JsonResponse A json array response including a boolean,errors array,warnings array.
    */
    public function validateGml(Request $request, $schema = 'http://schemas.opengis.net/gml/3.2.1/gml.xsd')
    {
        $gml = $request->request->get('gml');
        if (empty($gml)) {
            throw new BadRequestHttpException('No GML given. (Parameter:gml)');
        } else {
            $namespaces = array(
                'xmlns:gml' => 'http://www.opengis.net/gml/3.2',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:schemaLocation' => 'http://www.opengis.net/gml/3.2 ' . $schema
            );
            $gml = $this->addNamespace($gml, $namespaces);

            $errors = [];
            $warnings = [];
            $metadataUtil = $this->get('pelagos.util.metadata');
            $analysis = $metadataUtil->validateIso($gml, $schema);
            $errors = array_merge($errors, $analysis['errors']);
            $warnings = array_merge($warnings, $analysis['warnings']);

            $isValid = false;
            if (count($analysis['errors']) === 0) {
                $isValid = true;
            }
            return new JsonResponse(
                array(
                    $isValid,
                    $errors,
                    $warnings
                ),
                JsonResponse::HTTP_OK);
        }
    }

  /**
   * This function validate Geometry from a given wkt.
    *
    * @param Request $request The Symfony request object.
    *
    * @Method("POST")
    *
    * @Route("/validategeometryfromwkt")
    *
    * @throws BadRequestHttpException When no WKT is given.
    *
    * @return Response Includes boolean and invalid reason.
    */
    public function validateGeometryFromWktAction(Request $request)
    {
        $wkt = $request->request->get('wkt');
        if (!empty($wkt)) {
            try {
                \geoPHP::load($wkt, 'wkt');
            } catch (\Exception $exception) {
                return new Response(
                    preg_split('/:/', $exception->getMessage(), 2)[1],
                    response::HTTP_BAD_REQUEST,
                    ['content-type' => 'text/plain']
                );
            }
            $query = 'SELECT ST_IsValidReason(ST_GeomFromText(:wkt))';
            $connection = $this->getDoctrine()->getManager()->getConnection();
            $statement = $connection->prepare($query);
            $statement->bindValue('wkt', $wkt);
            $statement->execute();

            $results = $statement->fetchAll();
            $message = $results[0]['st_isvalidreason'];

            $returnCode = Response::HTTP_OK;
            if ($message !== 'Valid Geometry') {
                $returnCode = Response::HTTP_BAD_REQUEST;
            }
                return new Response(
                    $message,
                    $returnCode,
                    ['content-type' => 'text/plain']
                );
        } else {
            throw new BadRequestHttpException('No Well Know Text given. (Parameter:wkt)');
        }
    }
}

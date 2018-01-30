<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * A controller that does GML conversion.
 */
class GmlController extends Controller
{
    /**
     * The List the Lists action.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/gmltowkt")
     *
     * @Method("POST")
     *
     * @throws BadRequestHttpException When no GML is given.
     *
     * @return Response A list of Lists.
     */
    public function toWktAction(Request $request)
    {
        $gml = $request->request->get('gml');

        if ($gml !== null and $gml !== '') {
            $query = 'SELECT ST_asText(ST_GeomFromGML(:gml, 4326));';
            $connection = $this->getDoctrine()->getManager()->getConnection();
            $statement = $connection->prepare($query);
            $statement->bindValue('gml', $gml);
            $statement->execute();
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
     * The Research Group Generate List action.
     *
     * @param Request $request The Symfony request object.
     *
     * @Route("/wkttogml")
     *
     * @Method("POST")
     *
     * @throws BadRequestHttpException When no WKT is given.
     *
     * @return Response A list of Research Groups.
     */
    public function fromWktAction(Request $request)
    {
        $wkt = $request->request->get('wkt');

        if ($wkt !== null and $wkt !== '') {
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
   * This function validate GML from wkt.
   *
   * @param Request $request The Symfony request object.
   *
   * @Method("GET")
   *
   * @Route("/validategmlfromwkt")
   *
   * @throws BadRequestHttpException When no WKT is given.
   *
   * @return Response Includes boolean and invalid reason.
   */
    public function validateGmlFromWktAction(Request $request)
    {
        $params = $request->query->all();
        if (isset($params['wkt'])) {
            $query = 'SELECT ST_IsValidReason(ST_GeomFromText(:wkt))';
            $connection = $this->getDoctrine()->getManager()->getConnection();
            $statement = $connection->prepare($query);
            $statement->bindValue('wkt', $params['wkt']);
            $statement->execute();
            $results = $statement->fetchAll();
            $response = $results[0]['st_isvalidreason'];

            $returnCode = Response::HTTP_OK;
            if ($response !== 'Valid Geometry') {
                $returnCode = Response::HTTP_BAD_REQUEST;
            }
                return new Response(
                    $response,
                    $returnCode,
                    ['content-type' => 'text/plain']
                );
        } else {
            throw new BadRequestHttpException('No Well Know Text given. (Parameter:wkt)');
        }
    }
}

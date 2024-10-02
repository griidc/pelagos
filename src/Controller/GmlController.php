<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Exception\InvalidGmlException;
use App\Util\Geometry;
use App\Util\GmlUtil;
use App\Util\Metadata;
use Doctrine\ORM\EntityManagerInterface;

/**
 * A controller that does GML conversion.
 */
class GmlController extends AbstractController
{
    /**
     * Converting gml to wkt.
     *
     * @param Request $request The Symfony request object.
     *
     *
     * @throws BadRequestHttpException When no GML is given.
     * @return Response A response containing converted wkt.
     */
    #[Route(path: '/gmltowkt', name: 'pelagos_app_gml_towkt', methods: ['POST'])]
    public function toWktAction(Request $request, Geometry $geometryUtil)
    {
        $gml = $request->request->get('gml');

        if (!empty($gml)) {
            try {
                $wkt = $geometryUtil->convertGmlToWkt($gml);
            } catch (InvalidGmlException $e) {
                throw new BadRequestHttpException($e->getMessage());
            }
            return new JsonResponse(['wkt' => $wkt]);
        } else {
            throw new BadRequestHttpException('No GML given. (Parameter:gml)');
        }
    }

    /**
     * Converting wkt to gml.
     *
     * @param Request $request The Symfony request object.
     *
     *
     * @throws BadRequestHttpException When no WKT is given.
     * @return Response A response containing converted gml.
     */
    #[Route(path: '/wkttogml', name: 'pelagos_app_gml_fromwkt', methods: ['POST'])]
    public function fromWktAction(Request $request, EntityManagerInterface $entityManager)
    {
        $wkt = $request->request->get('wkt');

        if (!empty($wkt)) {
            $query = 'SELECT ST_asGML(3,ST_GeomFromText(:wkt,4326),6,17)';
            $connection = $entityManager->getConnection();
            $statement = $connection->prepare($query);
            $statement->bindValue('wkt', $wkt);
            try {
                $result = $statement->executeQuery();
            } catch (\Throwable $th) {
                throw new BadRequestHttpException("Error while trying to convert Well Known Text (wkt=$wkt)");
            }

            $results = $result->fetchAllAssociative();
            $gml = $results[0]['st_asgml'];
            $gml = $this->addGMLid($gml);

            return new JsonResponse(['gml' => $gml]);
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
    private function addGMLid(string $gml)
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
     * This function validates Gml against OpenGIS schema.
     *
     * @param Request  $request      The Symfony request object.
     * @param Metadata $metadataUtil The metadata util.
     * @param string   $schema       Url to remote schema validation cache.
     *
     * @throws BadRequestHttpException When no GML was given.
     *
     *
     * @return JsonResponse A json array response including a boolean,errors array,warnings array.
     */
    #[Route(path: '/validategml', name: 'pelagos_app_gml_validategml', methods: ['POST'])]
    public function validateGml(Request $request, Metadata $metadataUtil, string $schema = 'http://schemas.opengis.net/gml/3.2.1/gml.xsd')
    {
        $gml = $request->request->get('gml');
        $isValid = false;
        if (empty($gml)) {
            throw new BadRequestHttpException('No GML given. (Parameter:gml)');
        } else {
            $namespaces = array(
                'xmlns:gml' => 'http://www.opengis.net/gml/3.2',
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:schemaLocation' => 'http://www.opengis.net/gml/3.2 ' . $schema
            );

            $gml = GmlUtil::addNamespace($gml, $namespaces);

            $errors = [];
            $warnings = [];
            $analysis = $metadataUtil->validateIso($gml, $schema);
            $errors = array_merge($errors, $analysis['errors']);
            $warnings = array_merge($warnings, $analysis['warnings']);

            if (count($analysis['errors']) === 0) {
                $isValid = true;
            }
            return new JsonResponse(
                array(
                    $isValid,
                    $errors,
                    $warnings
                ),
                JsonResponse::HTTP_OK
            );
        }
    }

    /**
     * This function validate Geometry from a given wkt.
     *
     * @param Request $request The Symfony request object.
     *
     *
     * @throws BadRequestHttpException When no WKT is given.
     * @return Response Includes boolean and invalid reason.
     */
    #[Route(path: '/validategeometryfromwkt', name: 'pelagos_app_gml_validategeometryfromwkt', methods: ['POST'])]
    public function validateGeometryFromWktAction(Request $request, EntityManagerInterface $entityManager)
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
            $connection = $entityManager->getConnection();
            $statement = $connection->prepare($query);
            $statement->bindValue('wkt', $wkt);
            $result = $statement->executeQuery();

            $results = $result->fetchAllAssociative();
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

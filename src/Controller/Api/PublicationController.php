<?php

namespace App\Controller\Api;

use App\Util\PubLinkUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations\View;
use App\Entity\Publication;

/**
 * The Publication api controller.
 */
class PublicationController extends EntityController
{
    /**
     * Get a count of Publications.
     *
     * @param Request $request The request object.
     *
     *)
     *
     * @Route(
     *     "/api/publications/count",
     *     name="pelagos_api_publications_count",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @View()
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(Publication::class, $request);
    }

    /**
     * Get a collection of Publications.
     *
     * @param Request $request The request object.
     *
     * )
     *
     * @Route(
     *     "/api/publications",
     *     name="pelagos_api_publications_get_collection",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     * )
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @return Response
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(Publication::class, $request);
    }

    /**
     * Fetch and cache a publication with citation for a given DOI.
     *
     * @param Request     $request     A Symfony http request object, data includes the doi.
     * @param PubLinkUtil $pubLinkUtil The publication link utility.
     *
     * )
     *
     * @throws BadRequestHttpException If DOI in request is missing or not 10.something format.
     * @throws \Exception              Upon internal unexpected result.
     * @throws \Exception              If more than one cached publication found by DOI.
     * @throws NotFoundHttpException   If DOI couldn't be resolved by external DOI resolver.
     * @throws NotFoundHttpException   If external DOI service couldn't site this type of DOI (dataset doi).
     * @throws \Exception              Upon other DOI pull failure.
     *
     * @Route(
     *     "/api/publications",
     *     name="pelagos_api_publications_post",
     *     methods={"POST"},
     *     defaults={"_format"="json"}
     * )
     *
     * @return Publication
     */
    public function postAction(Request $request, PubLinkUtil $pubLinkUtil)
    {
        //query for a current publication/citation.

        $doi = $request->request->get('doi');

        if (preg_match('/^10\..*/', $doi) !== 1) {
            throw new BadRequestHttpException('Invalid format or missing DOI.');
        }

        // Attempt to get publication by DOI.
        $publications = $this->entityHandler->getBy(Publication::class, array('doi' => $doi));
        if (gettype($publications) == 'array') {
            // Case 1 - Data was previously cached.  Return cached copy instead, but lie a little about creation.
            if (count($publications) == 1) {
                $publication = $publications[0];
                // If ever needed, citation age is $publication->getModificationTimeStamp();

                return $this->makeCreatedResponse('pelagos_api_publications_get', $publication->getId());
            // Does not exist in cache.  Pull from external DOI resolver, cache and return citation.
            } elseif (count($publications) == 0) {
                $citationStruct = $pubLinkUtil->fetchCitation($doi);
                if (200 == $citationStruct['status']) {
                    $publication = new Publication($doi);

                    // somehow fill in the citation text here.
                    $publication->setCitationText($citationStruct['citation']);

                    $this->entityHandler->create($publication);

                    return $this->makeCreatedResponse('pelagos_api_publications_get', $publication->getId());
                } elseif (404 == $citationStruct['status']) {
                    throw new NotFoundHttpException('This DOI could not be found via external DOI resolution service. (404)');
                } elseif (406 == $citationStruct['status']) {
                    throw new NotFoundHttpException('The external DOI resolver could not generate a citation.  It is likely a dataset DOI. (406)');
                } else {
                    $errorText = $citationStruct['errorText'];
                    throw new \Exception('Unable to pull citation from external DOI resolution service.  Reason: ('
                        . $citationStruct['status'] . ") $errorText");
                }
            } else {
                throw new \Exception("Unexpected system error. DOI $doi references more than 1 cached Publication.");
            }
        } else {
            throw new \Exception('Unexpected system error. Expected array of Publications, but got something else.');
        }
    }

    /**
     * Get a single Publication.
     *
     * @param integer $id Entity ID for Publication.
     *
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @Route(
     *     "/api/publications/{id}",
     *     name="pelagos_api_publications_get",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Publication
     */
    public function getAction(int $id)
    {
        return $this->handleGetOne(Publication::class, $id);
    }

    /**
     * Retrieve a cached citation for a given DOI.
     *
     * @param Request $request A Symfony http request object, data includes the doi.
     *
     * )
     *
     * @throws \Exception            Upon internal unexpected result.
     * @throws NotFoundHttpException If cached citation could not be retrieved.
     * @throws \Exception            If more than one cached publication found by DOI.
     *
     * @Route(
     *     "/api/publications/cached/citation",
     *     name="pelagos_api_publications_get_cached_citation",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     * )

     * @return Publication|null
     */
    public function getCachedCitationAction(Request $request)
    {
        $doi = $request->query->get('doi');
        $publications = $this->entityHandler->getBy(Publication::class, array('doi' => $doi));
        if (gettype($publications) == 'array') {
            if (count($publications) == 1) {
                return new JsonResponse($publications[0]->getCitationText());
            } elseif (count($publications) > 1) {
                throw new \Exception("Unexpected system error. DOI $doi references more than 1 cached Publication.");
            } else {
                throw new NotFoundHttpException('This citation could not be found.');
            }
        } else {
            throw new \Exception('Unexpected system error. Expected array of Publications, but got something else.');
        }
    }
}

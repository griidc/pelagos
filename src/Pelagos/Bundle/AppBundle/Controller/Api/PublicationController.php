<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Doctrine\Common\Collections\Collection;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\Publication;
use Pelagos\Entity\PublicationCitation;

/**
 * The Publication api controller.
 */
class PublicationController extends EntityController
{
    /**
     * Fetch and cache a citation for a given DOI.
     *
     * @param Request $request A Symfony http request object, data includes the doi.
     *
     * @ApiDoc(
     *   section = "Publications",
     *   output = "Pelagos\Entity\PublicationCitation",
     *   statusCodes = {
     *     200 = "The requested Dataset was successfully retrieved.",
     *     404 = "The requested Dataset was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @throws \Exception If DOI in request is missing or not 10.something format.
     * @throws \Exception Upon internal unexpected result.
     * @throws \Exception If more than one cached publication found by DOI.
     * @throws \Exception If DOI couldn't be resolved by doi.org.
     * @throws \Exception If doi.org couldn't site this type of DOI (dataset doi).
     * @throws \Exception Upon other DOI pull failure.
     *
     * @return PublicationCitation
     */
    public function postAction(Request $request)
    {
        //query for a current publication/citation, if non-existent or too old,
        //re-pull from doi.org and re-cache.

        $doi = $request->request->get('doi');
        $pubLinkUtil = $this->get('pelagos.util.publink');
        $entityHandler = $this->get('pelagos.entity.handler');

        if (preg_match('/^10\./', $doi) != 1) {
            throw new \Exception('Invalid format or missing DOI.');
        }

        // Attempt to get publication by DOI.
        $publications = $entityHandler->getBy(Publication::class, array('doi' => $doi));
        if (gettype($publications) == 'array') {
            // Case 1 - Data was previously cached.  Return cached copy instead, but lie a little about creation.
            if (count($publications) == 1) {
                $publication = $publications[0];
                $publicationCitations = $publication->getCitations();

                $citation = $publicationCitations[0];
                $citationAge = $citation->getModificationTimeStamp();

                return $this->makeCreatedResponse('pelagos_api_publications_get', $publication->getId());
            // Does not exist in cache.  Pull from doi.org, cache and return citation.
            } elseif (count($publications == 0)) {
                $citationStruct = $pubLinkUtil->getCitationFromDoiDotOrg($doi);
                if (200 == $citationStruct['status']) {
                    $entityHandler = $this->get('pelagos.entity.handler');

                    $publication = new Publication($doi);
                    $entityHandler->create($publication);

                    $publicationCitation = $citationStruct['citation'];
                    $publicationCitation->setPublication($publication);

                    $entityHandler->create($publicationCitation);

                    return $this->makeCreatedResponse('pelagos_api_publications_get', $publication->getId());
                } elseif (404 == $citationStruct['status']) {
                    throw new \Exception('This DOI could not be found at doi.org. (404)');
                } elseif (406 == $citationStruct['status']) {
                    throw new \Exception('Doi.org could not generate a citation.  It is likely a dataset DOI. (406)');
                } else {
                    $errorText = $citationStruct['errorText'];
                    throw new \Exception('Unable to pull citation from doi.org.  Reason: ('
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
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return Publication
     */
    public function getAction($id)
    {
        return $this->handleGetOne(Publication::class, $id);
    }

    /**
     * Retrieve a cached citation for a given DOI.
     *
     * @param Request $request A Symfony http request object, data includes the doi.
     *
     * @ApiDoc(
     *   section = "Publications",
     *   output = "Pelagos\Entity\PublicationCitation",
     *   statusCodes = {
     *     200 = "The requested cached Publication citation was successfully retrieved.",
     *     404 = "The requested cached Publication citation was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @throws \Exception Upon internal unexpected result.
     * @throws \Exception If more than one cached publication found by DOI.
     *
     * @return PublicationCitation|null
     */
    public function getCachedCitationAction(Request $request)
    {
        $doi = $request->query->get('doi');
        $entityHandler = $this->get('pelagos.entity.handler');

        $publications = $entityHandler->getBy(Publication::class, array('doi' => $doi));
        if (gettype($publications) == 'array') {
            if (count($publications) == 1) {
                return new JsonResponse($publications[0]->getCitations()[0]->getCitationText());
            } elseif (count($publications) > 1) {
                throw new \Exception("Unexpected system error. DOI $doi references more than 1 cached Publication.");
            } else {
                return null;
            }
        } else {
            throw new \Exception('Unexpected system error. Expected array of Publications, but got something else.');
        }
    }
}

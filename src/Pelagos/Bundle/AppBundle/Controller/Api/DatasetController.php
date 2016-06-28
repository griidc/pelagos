<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\Dataset;

/**
 * The Dataset api controller.
 */
class DatasetController extends EntityController
{
    /**
     * Get a collection of Datasets.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Datasets",
     *   parameters = {
     *     {"name"="someProperty", "dataType"="string", "required"=false, "description"="Filter by someProperty"}
     *   },
     *   output = "array<Pelagos\Entity\Dataset>",
     *   statusCodes = {
     *     200 = "The requested collection of Datasets was successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("")
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return array
     */
    public function getCollectionAction(Request $request)
    {
        return $this->handleGetCollection(Dataset::class, $request);
    }

    /**
     * Get a single Dataset for a given id.
     *
     * @param integer $id The id of the Dataset to return.
     *
     * @ApiDoc(
     *   section = "Datasets",
     *   output = "Pelagos\Entity\Dataset",
     *   statusCodes = {
     *     200 = "The requested Dataset was successfully retrieved.",
     *     404 = "The requested Dataset was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return Dataset
     */
    public function getAction($id)
    {
        return $this->handleGetOne(Dataset::class, $id);
    }

    /**
     * Suggest a citation for a Dataset identified by UDI.
     *
     * @param integer $id The ID of the Dataset to suggest a citation for.
     *
     * @ApiDoc(
     *   section = "Datasets",
     *   statusCodes = {
     *     200 = "The requested Dataset Citation was successfully retrieved.",
     *     404 = "The requested Dataset was not found by the supplied UDI.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("/{id}/citation")
     *
     * @Rest\View
     *
     * @return string
     */
    public function getCitationAction($id)
    {
        $dataset = $this->handleGetOne(Dataset::class, $id);
        $submission = $dataset->getDatasetSubmission();

        $title = $dataset->getTitle();
        $udi = $dataset->getUdi();

        if ($submission instanceof DatasetSubmission) {
            $author = $submission->getAuthors();
            $year = $submission->getModificationTimeStamp()->format('Y');
            $doi = $submission->getDoi();

            // The doi could include a url - check for it
            if ($doi && strlen($doi) > 0) {
                // does the doi contain the string http
                $pos = strpos($doi, 'http');
                if ($pos === false) {
                    $url = 'http://dx.doi.org/' . $doi;
                } else {
                    $url = $doi;
                }
            } else {
                $url = 'http://data.gulfresearchinitiative.org/data/' . $udi;
            }

            $citationString = "$author ($year) $title ($udi)" .
                       "[Data files] Available from $url";

            return $citationString;
        } else {
            $citationString = "This dataset has no registration: $title ($udi)";
            return $citationString;
        }

    }
}

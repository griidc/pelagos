<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Bundle\AppBundle\Form\DatasetType;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

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
        return $dataset->getCitation();
    }

    /**
     * Update a Dataset with the submitted data.
     *
     * @param integer $id      The id of the Dataset to update.
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Datasets",
     *   input = {"class" = "Pelagos\Bundle\AppBundle\Form\DatasetType", "name" = ""},
     *   statusCodes = {
     *     204 = "The Dataset was successfully updated.",
     *     400 = "The request could not be processed due to validation or other errors.",
     *     403 = "The authenticated user was not authorized to edit the Person.",
     *     404 = "The requested Dataset was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(DatasetType::class, Dataset::class, $id, $request, 'PATCH');
        $jiraLinkValue = $request->request->get('issueTrackingTicket');
        if (null !== $jiraLinkValue) {

            $entityHandler = $this->get('pelagos.entity.handler');
            $mdappLogger = $this->get('pelagos.util.mdapplogger');

            $mdappLogger->writeLog(
                $this->getUser()->getUserName() .
                'set Jira Link for udi: ' .
                $entityHandler->get(Dataset::class, $id)->getUdi() .
                ' to ' .
                $jiraLinkValue .
                '.'
            );
        }
        return $this->makeNoContentResponse();
    }
}

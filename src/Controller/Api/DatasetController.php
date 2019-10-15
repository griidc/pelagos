<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations\View;

use App\Form\DatasetType;

use App\EventListener\EntityEventDispatcher;

use App\Entity\Dataset;
use App\Entity\DIF;
use App\Entity\DistributionPoint;
use App\Entity\PersonDatasetSubmissionDatasetContact;
use App\Entity\PersonDatasetSubmissionMetadataContact;

/**
 * The Dataset api controller.
 */
class DatasetController extends EntityController
{
    /**
     * Get a count of Datasets.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Datasets",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCountType",
     *     "name": "",
     *     "options": {
     *       "label": "Datasets",
     *       "data_class": "Pelagos\Entity\Dataset"
     *     }
     *   },
     *   statusCodes = {
     *     200 = "A count of Datasets was successfully returned.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route("/api/datasets/count", name="pelagos_api_datasets_count", methods={"GET"}, defaults={"_format"="json"})
     *
     * @View()
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(Dataset::class, $request);
    }

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
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @Route("/api/datasets", name="pelagos_api_datasets_get_collection", methods={"GET"})
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
     * @View(serializerEnableMaxDepthChecks = true)
     *
     * @Route("/api/datasets/{id}", name="pelagos_api_datasets_get", methods={"GET"}, defaults={"_format"="json"})
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
     * @Route("/api/datasets/{id}/citation", name="pelagos_api_datasets_get_citation", methods={"GET"})
     *
     * @View()
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
     * @Route("/api/datasets/{id}", name="pelagos_api_datasets_patch", methods={"PATCH"})
     *
     * @return Response A Response object with an empty body and a "no content" status code.
     */
    public function patchAction($id, Request $request)
    {
        $this->handleUpdate(DatasetType::class, Dataset::class, $id, $request, 'PATCH');
        $jiraLinkValue = $request->request->get('issueTrackingTicket');
        if (null !== $jiraLinkValue) {
            $mdappLogger = $this->get('pelagos.util.mdapplogger');

            $mdappLogger->writeLog(
                $this->getUser()->getUserName() .
                ' set Jira Link for udi: ' .
                $this->entityHandler->get(Dataset::class, $id)->getUdi() .
                ' to ' .
                $jiraLinkValue .
                '.' .
                ' (api msg)'
            );
        }
        return $this->makeNoContentResponse();
    }

    /**
     * Delete a Dataset and associated Metadata and Difs.
     *
     * @param integer $id The id of the Dataset to delete.
     * @param EntityEventDispatcher $entityEventDispatcher
     *
     * @return Response A response object with an empty body and a "no content" status code.
     * @ApiDoc(
     *   section = "Datasets",
     *   statusCodes = {
     *     204 = "The Dataset was successfully deleted.",
     *     403 = "You do not have sufficient privileges to delete this Dataset.",
     *     404 = "The requested Dataset was not found.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route("/api/datasets/{id}", name="pelagos_api_datasets_delete", methods={"DELETE"})
     */
    public function deleteAction($id, EntityEventDispatcher $entityEventDispatcher)
    {
        $dataset = $this->handleGetOne(Dataset::class, $id);

        $dif = $dataset->getDif();

        $datasetSubmissionHistory = $dataset->getDatasetSubmissionHistory();

        foreach ($datasetSubmissionHistory as $datasetSub) {
            $datasetContacts = $datasetSub->getDatasetContacts();
            foreach ($datasetContacts as $datasetContact) {
                $datasetContactId = $datasetContact->getId();
                $this->handleDelete(PersonDatasetSubmissionDatasetContact::class, $datasetContactId);
            }
            $metadataContacts = $datasetSub->getMetadataContacts();
            foreach ($metadataContacts as $metadataContact) {
                $metadataContactId = $metadataContact->getId();
                $this->handleDelete(PersonDatasetSubmissionMetadataContact::class, $metadataContactId);
            }
            $distributionPoints = $datasetSub->getDistributionPoints();
            foreach ($distributionPoints as $distributionPoint) {
                $distributionPointId = $distributionPoint->getId();
                $this->handleDelete(DistributionPoint::class, $distributionPointId);
            }
        }

        $entityEventDispatcher->dispatch($dataset, 'delete_doi');

        $this->handleDelete(Dataset::class, $id);

        if ($dif instanceof DIF) {
            $this->handleDelete(DIF::class, $dif->getId());
        }

        return $this->makeNoContentResponse();
    }
}

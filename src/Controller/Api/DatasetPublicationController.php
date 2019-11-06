<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Query;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetPublication;
use Pelagos\Entity\Publication;

/**
 * The Publication api controller.
 */
class DatasetPublicationController extends EntityController
{
    /**
     * Get a count of Publication to Dataset Associations.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Publication to Dataset Association",
     *   input = {
     *     "class": "Pelagos\Bundle\AppBundle\Form\EntityCountType",
     *     "name": "",
     *     "options": {
     *       "label": "Publication to Dataset Associations",
     *       "data_class": "Pelagos\Entity\DatasetPublication"
     *     }
     *   },
     *   statusCodes = {
     *     200 = "A count of Publication to Dataset Associations was successfully returned.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("/count")
     *
     * @Rest\View()
     *
     * @return integer
     */
    public function countAction(Request $request)
    {
        return $this->handleCount(DatasetPublication::class, $request);
    }

   /**
    * Get a collection of PublicationDatasets.
    *
    * @ApiDoc(
    *   section = "Publication to Dataset Association",
    *   parameters = {
    *     {"name"="someProperty", "dataType"="string", "required"=false, "description"="Filter by someProperty"}
    *   },
    *   output = "array<Pelagos\Entity\DatasetPublication>",
    *   statusCodes = {
    *     200 = "The requested collection of PublicationDatasets was successfully retrieved.",
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
    public function getCollectionAction()
    {
        $entityHandler = $this->get('pelagos.entity.handler');
        $collection = $entityHandler->getAll(
            DatasetPublication::class,
            array(
                'creationTimeStamp' => 'DESC'
            ),
            array(
                'dataset.researchGroup.fundingCycle.name',
                'dataset.researchGroup.name',
                'dataset.udi',
                'publication.doi',
                'creator.lastName',
                'creator.firstName',
                'creationTimeStamp'
                ),
            Query::HYDRATE_ARRAY
        );
        $data = array();
        foreach ($collection as $datasetPublication) {
            $dataset = $datasetPublication['dataset'];
            $linkId = $datasetPublication['id'];
            $fc = $dataset['researchGroup']['fundingCycle']['name'];
            $proj = $dataset['researchGroup']['name'];
            $udi = $dataset['udi'];
            $doi = $datasetPublication['publication']['doi'];
            $linkCreator = $datasetPublication['creator']['firstName'] .
                ' ' . $datasetPublication['creator']['lastName'];
            $createdOn = $datasetPublication['creationTimeStamp']->
                setTimezone(new \DateTimeZone('America/Chicago'))->format('m/d/y H:i:s') . ' CDT';
            $data[] = array(
                    'id' => $linkId,
                    'fc' => $fc,
                    'proj' => $proj,
                    'udi' => $udi,
                    'doi' => $doi,
                    'username' => $linkCreator,
                    'created' => $createdOn
                );
        }
        return $data;
    }

    /**
     * Link a Publication to a Dataset by their respective IDs.
     *
     * @param integer $id      Publication ID.
     * @param Request $request A Request object.
     *
     * @ApiDoc(
     *   section = "Publication to Dataset Association",
     *   parameters = {
     *                    {"name"="dataset",
     *                      "dataType"="integer",
     *                      "required"=true,
     *                      "description"="Numeric ID of Dataset to be linked."}
     *                },
     *   statusCodes = {
     *     204 = "The Publication has been linked to the Dataset.",
     *     400 = "The request could not be processed. (see message for reason)",
     *     404 = "The Publication requested could not be found.",
     *     403 = "The authenticated user was not authorized to create a Publication to Dataset link.",
     *     500 = "An internal error has occurred."
     *   }
     * )
     *
     * @Rest\View
     *
     * @throws BadRequestHttpException            If link already exists.
     * @throws BadRequestHttpException            If Dataset is not found internally.
     * @throws BadRequestHttpException            If database throws this specific uniqueness violation.
     * @throws UniqueConstraintViolationException If entity handler re-throws a this exception that isn't uniq_dataset_publication.
     *
     * @return Response A HTTP Response object.
     */
    public function linkAction($id, Request $request)
    {
        $datasetId = $request->query->get('dataset');

        try {
            $dataset = $this->handleGetOne(Dataset::class, $datasetId);
        } catch (NotFoundHttpException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $publication = $this->handleGetOne(Publication::class, $id);

        // Check for existing publink and throw bad request error if exists.
        $criteria = array('dataset' => $datasetId, 'publication' => $id);
        $publinks = $this->get('pelagos.entity.handler')->getBy(DatasetPublication::class, $criteria);
        if (count($publinks) > 0) {
            $existingPublink = $publinks[0];
            $createdOn = $existingPublink->getCreationTimeStamp()->format('m/d/Y H:i');
            $createdBy = $existingPublink->getCreator()->getFirstName()
                . ' ' . $existingPublink->getCreator()->getLastName();
            throw new BadRequestHttpException("Link already exists - created by $createdBy on $createdOn" . 'z');
        }

        $dataPub = new DatasetPublication($publication, $dataset);
        $entityHandler = $this->get('pelagos.entity.handler');
        try {
            $entityHandler->create($dataPub);
            // When a dataset to publication link is made the related dataset is reindex by Elastica.
            // This is done because of the way their relationship works, and change is not detected.
            $persister = $this->get('fos_elastica.object_persister.pelagos.dataset');
            $persister->insertOne($dataPub->getDataset());
        } catch (UniqueConstraintViolationException $e) {
            if (preg_match('/uniq_dataset_publication/', $e->getMessage())) {
                throw new BadRequestHttpException('Link already exists.');
            } else {
                throw $e;
            }
        }

        return $this->makeNoContentResponse();
    }

   /**
    * Delete a Publication to Dataset Association.
    *
    * @param integer $id The id of the Publication to Dataset Association to delete.
    *
    * @ApiDoc(
    *   section = "Publication to Dataset Association",
    *   statusCodes = {
    *     204 = "The Publication to Dataset Association was successfully deleted.",
    *     404 = "The requested Publication to Dataset Association was not found.",
    *     500 = "An internal error has occurred.",
    *   }
    * )
    *
    * @return Response A response object with an empty body and a "no content" status code.
    */
    public function deleteAction($id)
    {
        $this->handleDelete(DatasetPublication::class, $id);
        return $this->makeNoContentResponse();
    }
}

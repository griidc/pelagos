<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Query;

use Symfony\Component\Routing\Annotation\Route;

use FOS\RestBundle\Controller\Annotations\View;

use FOS\ElasticaBundle\Persister\ObjectPersister;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use App\Entity\Dataset;
use App\Entity\DatasetPublication;
use App\Entity\Publication;

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
     * @Route(
     *     "/api/dataset_publications/count",
     *     name="pelagos_api_dataset_publications_count",
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
    * @Route(
    *     "/api/dataset_publications",
    *     name="pelagos_api_dataset_publications_get_collection",
    *     methods={"GET"},
    *     defaults={"_format"="json"}
    *     )
    *
    * @View(serializerEnableMaxDepthChecks = true)
    *
    * @return array
    */
    public function getCollectionAction()
    {
        $collection = $this->entityHandler->getAll(
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
            $createdOn = $datasetPublication['creationTimeStamp'];
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
     * @param integer         $id              Publication ID.
     * @param Request         $request         A Request object.
     * @param ObjectPersister $objectPersister The object persister.
     *
     * @throws UniqueConstraintViolationException If entity handler re-throws a this exception that is not uniq_dataset_publication.
     * @throws BadRequestHttpException If link already exists.
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
     * @Route(
     *     "/api/dataset_publications/{id}",
     *     name="pelagos_api_dataset_publications_link",
     *     methods={"LINK"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return Response A HTTP Response object.
     */
    public function linkAction(int $id, Request $request, ObjectPersister $objectPersister)
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
        $publinks = $this->entityHandler->getBy(DatasetPublication::class, $criteria);
        if (count($publinks) > 0) {
            $existingPublink = $publinks[0];
            $createdOn = $existingPublink->getCreationTimeStamp()->format('m/d/Y H:i');
            $createdBy = $existingPublink->getCreator()->getFirstName()
                . ' ' . $existingPublink->getCreator()->getLastName();
            throw new BadRequestHttpException("Link already exists - created by $createdBy on $createdOn" . 'z');
        }

        $dataPub = new DatasetPublication($publication, $dataset);
        try {
            $this->entityHandler->create($dataPub);
            // When a dataset to publication link is made the related dataset is reindex by Elastica.
            // This is done because of the way their relationship works, and change is not detected.
            $objectPersister->insertOne($dataPub->getDataset());
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
    * @Route(
    *     "/api/dataset_publications/{id}",
    *     name="pelagos_api_dataset_publications_delete",
    *     methods={"DELETE"},
    *     defaults={"_format"="json"}
    *     )
    *
    * @return Response A response object with an empty body and a "no content" status code.
    */
    public function deleteAction(int $id)
    {
        $this->handleDelete(DatasetPublication::class, $id);
        return $this->makeNoContentResponse();
    }
}

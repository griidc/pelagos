<?php

namespace App\Controller\Api;

use App\Entity\File;
use App\Entity\InformationProduct;
use App\Entity\ResearchGroup;
use App\Form\InformationProductType;
use App\Repository\InformationProductRepository;
use App\Util\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializationContext;

class InformationProductController extends AbstractFOSRestController
{

    /**
     * Get Information Product.
     *
     * @param InformationProduct  $informationProduct The id of the information product.
     * @param SerializerInterface $serializer         JMS Serializer instance.
     *
     * @Route (
     *     "/api/information_product/{id}",
     *     name="pelagos_api_get_information_product",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     * )
     *
     * @return Response
     */
    public function getInformationProduct(InformationProduct $informationProduct, SerializerInterface $serializer): Response
    {
        $context = SerializationContext::create();
        $context->enableMaxDepthChecks();
        $context->setSerializeNull(true);

        return new Response($serializer->serialize($informationProduct, 'json', $context));
    }

    /**
     * Creates a new Information Product
     *
     * @param Request $request
     *
     * @return Response
     *
     * @Route (
     *     "/api/information_product",
     *     name="pelagos_api_create_information_product",
     *     methods={"POST"},
     *     defaults={"_format"="json"},
     * )
     */
    public function createInformationProduct(Request $request): Response
    {
        $response = Response::HTTP_BAD_REQUEST;
        $id = null;
        $prefilledRequestDataBag = $this->jsonToRequestDataBag($request->getContent());
        $entityManager = $this->getDoctrine()->getManager();
        $informationProduct = new InformationProduct();
        $form = $this->createForm(InformationProductType::class, $informationProduct);
        $request->request->set($form->getName(), $prefilledRequestDataBag);
        $researchGroupsIds = $request->get('selectedResearchGroups');
        $researchGroups = $entityManager->getRepository(ResearchGroup::class)->findBy(['id' => $researchGroupsIds]);
        foreach ($researchGroups as $researchGroup) {
            $informationProduct->addResearchGroup($researchGroup);
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($informationProduct);
            $entityManager->flush();
            $id = $informationProduct->getId();
            $response = Response::HTTP_CREATED;
        }

        return new JsonResponse(['id' => $id], $response);
    }

    /**
     * Updates the Information Product
     *
     * @param Request $request
     * @param InformationProduct $informationProduct
     *
     * @return Response
     *
     * * @Route (
     *     "/api/information_product/{id}",
     *     name="pelagos_api_update_information_product",
     *     methods={"PATCH"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     * )
     */
    public function updateInformationProduct(Request $request, InformationProduct $informationProduct): Response
    {
        $form = $this->createForm(InformationProductType::class, $informationProduct);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * Delete Information Product
     *
     * @param Request $request
     * @param InformationProduct $informationProduct
     *
     * @return Response
     *
     * @Route (
     *     "/api/information_product/{id}",
     *     name="pelagos_api_deletet_information_product",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     * )
     */
    public function deleteInformationProduct(Request $request, InformationProduct $informationProduct): Response
    {
        if ($this->isCsrfTokenValid('delete'.$informationProduct->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($informationProduct);
            $entityManager->flush();

            return new JsonResponse(Response::HTTP_OK);
        }
    }

    /**
     * Will output an array which can be inserted into the @param string $json
     * @return array
     * @throws \Exception
     * @throws Exception*@see Request::request::set
     * Such request can be then passed to proper form @see FormInterface::handleRequest()
     * With this - data sent via axios post can be processed like it normally should like via standard POST call
     *
     */
    private function jsonToRequestDataBag(string $json): array
    {
        $dataArray = json_decode($json, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $message = "Provided json is not valid";
            $this->logger->critical($message, [
                'jsonLastErrorMessage' => json_last_error_msg(),
            ]);

            throw new \Exception($message, Response::HTTP_BAD_REQUEST);
        }

        return $dataArray;
    }

    /**
     * Find Information Product by associated research group id.
     *
     * @param ResearchGroup                $researchGroup                The id of the research group.
     * @param SerializerInterface          $serializer                   JMS serializer instance.
     * @param InformationProductRepository $informationProductRepository Entity repository to get the entity.
     * @Route (
     *     "/api/information_product_by_research_group_id/{id}",
     *     name="pelagos_api_get_information_product_by_research_group_id",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     * )
     *
     * @return Response
     */
    public function getInformationProductByResearchGroupId(
        ResearchGroup $researchGroup,
        SerializerInterface $serializer,
        InformationProductRepository $informationProductRepository
    ): Response {
        $context = SerializationContext::create();
        $context->enableMaxDepthChecks();
        $context->setSerializeNull(true);
        $informationProducts = $informationProductRepository->findOneByResearchGroupId($researchGroup->getId());

        return new Response($serializer->serialize($informationProducts, 'json', $context));
    }

    /**
     * Adds a file to a dataset submission.
     *
     * @param Request                $request           The request body sent with file metadata.
     * @param EntityManagerInterface $entityManager     Entity manager interface to doctrine operations.
     * @param FileUploader           $fileUploader      File upload handler service.
     *
     * @Route(
     *     "/api/add_file_to_information_product",
     *     name="pelagos_api_add_file_information_product",
     *     methods={"POST"}
     *     )
     *
     * @IsGranted("ROLE_DATA_REPOSITORY_MANAGER")
     *
     * @return Response
     */
    public function addFileToInformationProduct(Request $request, EntityManagerInterface $entityManager, FileUploader $fileUploader) : Response
    {
        try {
            $fileMetadata = $fileUploader->combineChunks($request);
        } catch (\Exception $exception) {
            return new JsonResponse(['code' => 400, 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $fileName = $fileMetadata['name'];
        $filePath = $fileMetadata['path'];
        $fileSize = $fileMetadata['size'];

        $newFile = new File();
        $newFile->setFilePathName(trim($fileName));
        $newFile->setFileSize($fileSize);
        $newFile->setUploadedAt(new \DateTime('now'));
        $newFile->setUploadedBy($this->getUser()->getPerson());
        $newFile->setPhysicalFilePath($filePath);
        $newFile->setDescription('Information Product File');
        $entityManager->persist($newFile);
        $entityManager->flush();

        $id = $newFile->getId();
        return new JsonResponse(array("id" => $id));
    }
}

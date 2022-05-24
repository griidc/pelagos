<?php

namespace App\Controller\Api;

use App\Entity\DigitalResourceTypeDescriptor;
use App\Entity\File;
use App\Entity\InformationProduct;
use App\Entity\ProductTypeDescriptor;
use App\Entity\ResearchGroup;
use App\Form\InformationProductType;
use App\Message\DeleteFile;
use App\Message\InformationProductFiler;
use App\Repository\FileRepository;
use App\Repository\InformationProductRepository;
use App\Util\Datastore;
use App\Util\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

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
     * @param Request             $request    The Request.
     * @param MessageBusInterface $messageBus The message bus.
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
    public function createInformationProduct(Request $request, MessageBusInterface $messageBus): Response
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
        $productTypeDescriptorIds = $request->get('selectedProductTypes');
        $productTypeDescriptors = $entityManager->getRepository(ProductTypeDescriptor::class)->findBy(['id' => $productTypeDescriptorIds]);
        $this->checkIfDescriptorExists($productTypeDescriptorIds, $productTypeDescriptors, ProductTypeDescriptor::FRIENDLY_NAME);
        foreach ($productTypeDescriptors as $productTypeDescriptor) {
            $informationProduct->addProductTypeDescriptor($productTypeDescriptor);
        }
        $digitalResourceTypeDescriptorIds = $request->get('selectedDigitalResourceTypes');
        $digitalResourceTypeDescriptors = $entityManager->getRepository(DigitalResourceTypeDescriptor::class)->findBy(['id' => $digitalResourceTypeDescriptorIds]);
        $this->checkIfDescriptorExists($digitalResourceTypeDescriptorIds, $digitalResourceTypeDescriptors, DigitalResourceTypeDescriptor::FRIENDLY_NAME);
        foreach ($digitalResourceTypeDescriptors as $digitalResourceTypeDescriptor) {
            $informationProduct->addDigitalResourceTypeDescriptor($digitalResourceTypeDescriptor);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $informationProduct->setCreator($this->getUser()->getPerson());
            $file = $informationProduct->getFile();
            if ($file instanceof File) {
                $file->setStatus(file::FILE_IN_QUEUE);
            }
            $entityManager->persist($informationProduct);
            $entityManager->flush();
            $id = $informationProduct->getId();
            $response = Response::HTTP_CREATED;
        }

        $messageBus->dispatch(new InformationProductFiler($informationProduct->getId()));

        return new JsonResponse(['id' => $id], $response);
    }

    /**
     * Updates the Information Product
     *
     * @param Request             $request            The Request.
     * @param InformationProduct  $informationProduct The information product to update.
     * @param MessageBusInterface $messageBus         The message bus.
     *
     * @IsGranted("ROLE_DATA_REPOSITORY_MANAGER")
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
    public function updateInformationProduct(Request $request, InformationProduct $informationProduct, MessageBusInterface $messageBus): Response
    {
        $prefilledRequestDataBag = $this->jsonToRequestDataBag($request->getContent());
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(InformationProductType::class, $informationProduct, ['method' => 'PATCH']);
        $request->request->set($form->getName(), $prefilledRequestDataBag);
        $researchGroupsIds = $request->get('selectedResearchGroups');
        $researchGroupsToBeDeleted = $entityManager->getRepository(ResearchGroup::class)->findBy(['id' => $informationProduct->getResearchGroupList()]);
        $researchGroupsToBeAdded = $entityManager->getRepository(ResearchGroup::class)->findBy(['id' => $researchGroupsIds]);
        // Remove previously added research groups
        foreach ($researchGroupsToBeDeleted as $researchGroup) {
            $informationProduct->removeResearchGroup($researchGroup);
        }
        // Add them from the newly updated Information product
        foreach ($researchGroupsToBeAdded as $researchGroup) {
            $informationProduct->addResearchGroup($researchGroup);
        }
        $productTypeDescriptorIds = $request->get('selectedProductTypes');
        $productTypeDescriptorsToBeDeleted = $entityManager->getRepository(ProductTypeDescriptor::class)->findBy(['id' => $informationProduct->getProductTypeDescriptorList()]);
        $productTypeDescriptorsToBeAdded = $entityManager->getRepository(ProductTypeDescriptor::class)->findBy(['id' => $productTypeDescriptorIds]);
        $this->checkIfDescriptorExists($productTypeDescriptorIds, $productTypeDescriptorsToBeAdded, ProductTypeDescriptor::FRIENDLY_NAME);
        // Remove previously added product type descriptors
        foreach ($productTypeDescriptorsToBeDeleted as $productTypeDescriptor) {
            $informationProduct->removeProductTypeDescriptor($productTypeDescriptor);
        }
        // Add them from the newly updated product type descriptor
        foreach ($productTypeDescriptorsToBeAdded as $productTypeDescriptor) {
            $informationProduct->addProductTypeDescriptor($productTypeDescriptor);
        }
        $digitalResourceTypeDescriptorIds = $request->get('selectedDigitalResourceTypes');
        $digitalResourceTypeDescriptorsToBeDeleted = $entityManager->getRepository(DigitalResourceTypeDescriptor::class)->findBy(['id' => $informationProduct->getDigitalResourceTypeDescriptorList()]);
        $digitalResourceTypeDescriptorsToBeAdded = $entityManager->getRepository(DigitalResourceTypeDescriptor::class)->findBy(['id' => $digitalResourceTypeDescriptorIds]);
        $this->checkIfDescriptorExists($digitalResourceTypeDescriptorIds, $digitalResourceTypeDescriptorsToBeAdded, DigitalResourceTypeDescriptor::FRIENDLY_NAME);
        // Remove previously added digital resource type descriptors
        foreach ($digitalResourceTypeDescriptorsToBeDeleted as $digitalResourceTypeDescriptor) {
            $informationProduct->removeDigitalResourceTypeDescriptor($digitalResourceTypeDescriptor);
        }
        // Add them from the newly updated digital resource type descriptor
        foreach ($digitalResourceTypeDescriptorsToBeAdded as $digitalResourceTypeDescriptor) {
            $informationProduct->addDigitalResourceTypeDescriptor($digitalResourceTypeDescriptor);
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
        }

        $messageBus->dispatch(new InformationProductFiler($informationProduct->getId()));

        return new JsonResponse([], Response::HTTP_OK);
    }

    /**
     * Delete Information Product
     *
     * @param Request            $request
     * @param InformationProduct $informationProduct
     *
     * @return Response
     *
     * @Route (
     *     "/api/information_product/{id}",
     *     name="pelagos_api_delete_information_product",
     *     methods={"DELETE"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     * )
     */
    public function deleteInformationProduct(Request $request, InformationProduct $informationProduct): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($informationProduct);
        $entityManager->flush();
        return new JsonResponse(Response::HTTP_OK);
    }

    /**
     * Get all Information Products.
     *
     * @param Request $request The Request.

     * @return Response
     *
     * @Route (
     *     "/api/information_products",
     *     name="pelagos_api_get_all_information_product",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     * )
     */
    public function getAllInformationProducts(InformationProductRepository $informationProductRepository, SerializerInterface $serializer)
    {
        $context = SerializationContext::create();
        $context->enableMaxDepthChecks();
        $context->setSerializeNull(true);

        $informationProducts = $informationProductRepository->findAll();

        return new Response($serializer->serialize($informationProducts, 'json', $context));
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
     * @param Request                      $request                      The request body sent with file metadata.
     * @param EntityManagerInterface       $entityManager                Entity manager interface to doctrine operations.
     * @param FileUploader                 $fileUploader                 File upload handler service.
     * @param InformationProductRepository $informationProductRepository The information product repository.
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
    public function addFileToInformationProduct(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        InformationProductRepository $informationProductRepository
    ) : Response {
        try {
            $fileMetadata = $fileUploader->combineChunks($request);
        } catch (\Exception $exception) {
            return new JsonResponse(['code' => 400, 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $informationProductId = $request->get('informationProductId');
        if ($informationProductId) {
            $informationProduct = $informationProductRepository->find($informationProductId);
        } else {
            $informationProduct = null;
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
        $newFile->setCreator($this->getUser()->getPerson());
        $entityManager->persist($newFile);
        if ($informationProduct instanceof InformationProduct) {
            $informationProduct->setFile($newFile);
        }

        $entityManager->flush();

        $id = $newFile->getId();
        return new JsonResponse(array("id" => $id));
    }

    /**
     * Download a file.
     *
     * @param InformationProduct $informationProduct The information product, that has the file.
     *
     * @Route(
     *     "/api/information_product_file_download/{id}",
     *     name="pelagos_api_ip_file_download",
     *     methods={"GET"},
     *     requirements={"id"="\d+"}
     *     )
     *
     * @throws BadRequestHttpException When file is not found.
     *
     * @return Response
     */
    public function downloadFile(InformationProduct $informationProduct, Datastore $datastore): Response
    {
        $file = $informationProduct->getFile();
        if (!$file instanceof File) {
            throw new BadRequestHttpException('File not found!');
        }
        $filePhysicalPath = $file->getPhysicalFilePath();
        $filename = $file->getFilePathName();
        $response = new StreamedResponse(function () use ($file, $datastore) {
            $outputStream = fopen('php://output', 'wb');
            if ($file->getStatus() === File::FILE_DONE) {
                try {
                    $fileStream = $datastore->getFile($file->getPhysicalFilePath())['fileStream'];
                } catch (\Exception $exception) {
                    throw new BadRequestHttpException($exception->getMessage());
                }
            } else {
                $fileStream = fopen($file->getPhysicalFilePath(), 'r');
            }
            stream_copy_to_stream($fileStream, $outputStream);
        });

        $mimeType = $datastore->getMimeType($filePhysicalPath) ?: 'application/octet-stream';

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            basename($filename),
        );

        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-type', $mimeType);

        return $response;
    }

    /**
     * Delete a file or folder.
     *
     * @param Request                      $request                      The request body sent with file metadata.
     * @param EntityManagerInterface       $entityManager                Entity manager interface instance.
     * @param MessageBusInterface          $messageBus                   Message bus interface.
     * @param InformationProductRepository $informationProductRepository The information product repository.
     * @param FileRepository               $fileRepository               The file repository.
     *
     * @Route(
     *     "/api/information_product_file_delete",
     *     name="pelagos_api_ip_file_delete",
     *     methods={"DELETE"},
     *     )
     *
     * @throws BadRequestException When the file doesn't exist, or is not the right kind of file.
     *
     * @IsGranted("ROLE_DATA_REPOSITORY_MANAGER")
     *
     * @return Response
     */
    public function deleteInformationProductFile(
        Request $request,
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        InformationProductRepository $informationProductRepository,
        FileRepository $fileRepository
    ): Response {
        $informationProductId = $request->get('informationProductId');
        $fileId = $request->get('fileId');

        if (is_numeric($informationProductId)) {
            $informationProduct = $informationProductRepository->find($informationProductId);
            $file = $informationProduct->getFile();
        } elseif ($fileId) {
            $informationProduct = null;
            $file = $fileRepository->find($fileId);
            if ($file instanceof File and $file->getStatus() !== File::FILE_NEW) {
                throw new BadRequestHttpException('Without the IP, I can only delete new files!');
            }
        } else {
            throw new BadRequestHttpException('No parameters given, need File or IP!');
        }

        if (!$file instanceof File) {
            throw new BadRequestHttpException('No file attached for this IP!');
        }

        if ($informationProduct instanceof InformationProduct) {
            $informationProduct->setFile(null);
        }
        $this->deleteFile($file, $messageBus);
        $entityManager->remove($file);
        $entityManager->flush();


        return new Response(
            null,
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * Delete individual file from disk or mark as deleted.
     *
     * @param File                $file       File entity that needs to be deleted.
     * @param MessageBusInterface $messageBus Message bus interface.
     *
     * @throws BadRequestHttpException If the file could not be deleted.
     *
     * @return void
     */
    private function deleteFile(File $file, MessageBusInterface $messageBus) : void
    {
        if ($file->getStatus() === File::FILE_NEW) {
            $deleteFile = unlink($file->getPhysicalFilePath());
            $deleteFolder = rmdir(dirname($file->getPhysicalFilePath()));
            if (!$deleteFile or !$deleteFolder) {
                throw new BadRequestHttpException('Unable to delete file');
            }
        } elseif ($file->getStatus() === File::FILE_DONE) {
            $deleteMessage = new DeleteFile($file->getPhysicalFilePath());
            $messageBus->dispatch($deleteMessage);
        }
    }


    /**
     * Check if descriptor type or product type exists.
     *
     * @param array  $idsSelected          List of descriptor ids that need to be selected.
     * @param array  $descriptorsToBeAdded List of descriptor that needs to be added.
     * @param string $friendlyName         Friendly name for entity.
     *
     * @return void
     */
    private function checkIfDescriptorExists(array $idsSelected, array $descriptorsToBeAdded, string $friendlyName): void
    {
        $descriptorsToBeAddedList = [];
        if (count($descriptorsToBeAdded) !== count($idsSelected)) {
            foreach ($descriptorsToBeAdded as $descriptor) {
                $descriptorsToBeAddedList[] = $descriptor->getId();
            }

            $idDifference = array_diff($idsSelected, $descriptorsToBeAddedList);
            if (!empty($idDifference)) {
                throw new BadRequestHttpException(
                    'Selected Entity ' . $friendlyName . 'with ids:'. implode(" ", $idDifference)  . ', does not exist!'
                );
            }
        }
    }
}

<?php

namespace App\Controller\Api;

use App\Entity\InformationProduct;
use App\Form\InformationProductType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class InformationProductController extends AbstractFOSRestController
{

    /**
     * @param InformationProduct $informationProduct The id of the information product.
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
    public function getInformationProduct(InformationProduct $informationProduct): Response
    {
        return new JsonResponse($informationProduct);
    }

    /**
     * Creates a new Information Product
     *
     * @param Request $request
     *
     * @return Response
     *
     * @Route (
     *     "/api/information_product/{id}",
     *     name="pelagos_api_create_information_product",
     *     methods={"POST"},
     *     defaults={"_format"="json"},
     *     requirements={"id"="\d+"}
     * )
     */
    public function createInformationProduct(Request $request): Response
    {
        $informationProduct = new InformationProduct();
        $form = $this->createForm(InformationProductType::class, $informationProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($informationProduct);
            $entityManager->flush();

            $id = $informationProduct->getId();

            return new JsonResponse(["id"=>$id], Response::HTTP_CREATED);
        }
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
}

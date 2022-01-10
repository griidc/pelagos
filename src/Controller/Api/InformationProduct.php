<?php

namespace App\Controller\Api;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InformationProduct extends AbstractFOSRestController
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
        return new Response(
            json_encode($informationProduct),
            Response::HTTP_OK,
            array(
            'Content-Type' => 'application/json',
                )
        );

    }

    public function createInformationProduct()
    {
        // TO DO
    }

    public function updateInformationProduct()
    {
        // TO DO
    }

    public function deleteInformationProduct()
    {
        // TO DO
    }
}

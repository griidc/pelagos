<?php

namespace App\Controller\UI;

use App\Util\InformationProductSearch;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InformationProductSearchController extends AbstractController
{
    /**
     * @Route("/ip-search", name="app_information_product_search")
     */
    public function index(): Response
    {
        return $this->render('information_product_search/index.html.twig', [
            'controller_name' => 'InformationProductSearchController',
        ]);
    }

    /**
     * @Route("/api/ip_search", name="app_information_product_search_api")
     *
     * @return Response
     */
    public function searchForInformationProduct(Request $request, InformationProductSearch $informationProductSearch, SerializerInterface $serializer): Response
    {
        $queryString = $request->query->get('queryString');
        $page = $request->query->get('page');

        $page = $page ?? 1;

        $userPaginator = $informationProductSearch->findInformationProduct($queryString);
        $userPaginator->setCurrentPage($page);
        $informationProducts = $userPaginator->getCurrentPageResults();

        $context = SerializationContext::create();
        $context->enableMaxDepthChecks();
        $context->setSerializeNull(true);

        $result = array();

        $result["result"] = $userPaginator->getNbResults();
        $result["pages"] = $userPaginator->getNbPages();
        $result["resultPerPage"] = $userPaginator->getMaxPerPage();
        $result["informationProducts"] = $informationProducts;

        $json = $serializer->serialize($result, 'json', $context);

        $header = array('Content-Type', 'application/json');

        return new Response($json, Response::HTTP_OK, $header);

    }
}

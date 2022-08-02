<?php

namespace App\Controller\UI;

use App\Search\InformationProductSearch;
use App\Search\SearchOptions;
use App\Util\JsonSerializer;
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
    public function searchForInformationProduct(Request $request, InformationProductSearch $informationProductSearch, JsonSerializer $jsonSerializer): Response
    {
        $queryString = $request->query->get('queryString');
        $page = $request->query->get('page');
        $researchGroupFilter = $request->query->get('researchGroups');
        $productTypeDescFilter = $request->query->get('productTypeDesc');
        $digitalTypeDescFilter = $request->query->get('digitalTypeDesc');

        $searchOptions = new SearchOptions($queryString);
        $searchOptions->setCurrentPage($page);
        $searchOptions->setResearchGroupFilter($researchGroupFilter);
        $searchOptions->setProductTypeDescFilter($productTypeDescFilter);
        $searchOptions->setDigitalTypeDescFilter($digitalTypeDescFilter);

        $searchOptions->setFacets(array('researchGroup'));

        $searchResults = $informationProductSearch->search($searchOptions);

        $groups = $groups = array(
            'Default',
            'search',
            'result' => array(
                'search',
                'researchGroup' => array(
                    'search',
                ),
            ),
        );

        return $jsonSerializer->serialize($searchResults, $groups)->createJsonResponse();
    }
}

<?php

namespace App\Controller\UI;

use App\Search\MultiSearch;
use App\Search\SearchOptions;
use App\Util\JsonSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MultiSearchController extends AbstractController
{
    /**
     * @Route("/multi-search", name="app_multi_search")
     */
    public function index(): Response
    {
        return $this->render('MultiSearch/index.html.twig', [
            'controller_name' => 'MultiSearchController',
        ]);
    }

    /**
     * @Route("/api/multi_search", name="app_multi_search_api")
     *
     * @return Response
     */
    public function search(Request $request, MultiSearch $multiSearch, JsonSerializer $jsonSerializer): Response
    {
        $queryString = (string) $request->query->get('queryString');
        $page = (int) $request->query->get('page');
        $numberOfResultsPerPage = (int) $request->query->get('perPage');
        $researchGroupFilter = (string) $request->query->get('researchGroup');
        $funderFilter = (string) $request->query->get('funder');
        $dataTypeFilter = (string) $request->query->get('dataType');
        $datasetStatusFilter = (string) $request->query->get('status');
        $datasetTags = (string) $request->query->get('tags');
        $dateType = (string) $request->query->get('dateType');
        $rangeStartDate = (string) $request->query->get('rangeStartDate');
        $rangeEndDate = (string) $request->query->get('rangeEndDate');
        $sortOrder = (string) $request->query->get('sortOrder');
        $field = (string) $request->query->get('field');

        $searchOptions = new SearchOptions($queryString);
        $searchOptions->setCurrentPage($page);
        $searchOptions->setResearchGroupFilter($researchGroupFilter);
        $searchOptions->setFunderFilter($funderFilter);
        $searchOptions->setDataType($dataTypeFilter);
        $searchOptions->setDatasetStatus($datasetStatusFilter);
        $searchOptions->setMaxPerPage($numberOfResultsPerPage);
        $searchOptions->setTags($datasetTags);
        $searchOptions->setDateType($dateType);
        $searchOptions->setRangeStartDate($rangeStartDate);
        $searchOptions->setRangeEndDate($rangeEndDate);
        $searchOptions->setSortOrder($sortOrder);
        $searchOptions->setField($field);

        $searchResults = $multiSearch->search($searchOptions);
        $groups = array(
            'Default',
            'search',
            'result' => array(
                'search',
                'card',
                'researchGroup' => array(
                    'search',
                ),
                'researchGroups' => array(
                    'search',
                ),
                'digitalResourceTypeDescriptors' => array(
                    'search',
                ),
                'productTypeDescriptors' => array(
                    'search',
                ),
                'file' => array(
                    'card',
                ),
                'doi' => array(
                    'doi',
                ),
                'datasetSubmission' => array(
                    'authors',
                    'coldStorage',
                    'card',
                ),
            ),
        );

        return $jsonSerializer->serialize($searchResults, $groups)->createJsonResponse();
    }
}

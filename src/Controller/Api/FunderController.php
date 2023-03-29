<?php

namespace App\Controller\Api;

use App\Repository\FunderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This is a Funder API controller class.
 */
class FunderController extends AbstractController
{
    /**
     * Get funders by name API call.
     */
    #[Route('/api/funders', name: 'app_api_funders_by_name')]
    public function getFunderByName(Request $request, FunderRepository $funderRepository): Response
    {
        $userQueryString = $request->query->get('queryString');
        $funders = $funderRepository->findFunderByPartialName($userQueryString);

        return new JsonResponse($funders);
    }
}

<?php

namespace App\Controller\Api;

use App\Repository\FunderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FunderController extends AbstractController
{
    #[Route('/api/funders', name: 'app_api_funders')]
    public function getFunderByName(Request $request, FunderRepository $funderRepository, SerializerInterface $serializer): Response
    {
        $queryString = $request->get('queryString');
        $expressionBuilder = Criteria::expr();
        $expression = $expressionBuilder->contains('name', $queryString);
        $criteria = new Criteria($expression);
        $funders = $funderRepository->matching($criteria);
        $json = json_encode($funders->toArray());
        dd($json);

        return new JsonResponse($funders->toArray());
    }
}
